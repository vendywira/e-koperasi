<?php

namespace App\Http\Controllers;

use App\Mail\TicketCreatedMail;
use App\Mail\TicketStatusChangedMail;
use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $tickets = Ticket::where('user_id', $user->id)
            ->with(['replies' => function ($q) {
                $q->latest();
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Client/Tickets/Index', [
            'tickets' => $tickets,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Client/Tickets/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,mp4,mov,avi',
        ]);

        $ticket = Ticket::create([
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => 'pending',
        ]);

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('tickets/' . $ticket->id, 'public');
                Attachment::create([
                    'attachable_id' => $ticket->id,
                    'attachable_type' => Ticket::class,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        // Send email to client
        Mail::to($ticket->user->email)->queue(
            new TicketCreatedMail($ticket, 'client')
        );

        // Send email to all admin & it-ops
        $staffUsers = User::whereIn('role', ['admin', 'it-ops'])->get();
        foreach ($staffUsers as $staff) {
            Mail::to($staff->email)->queue(
                new TicketCreatedMail($ticket, 'staff')
            );
        }

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Ticket berhasil dibuat.');
    }

    public function show(Request $request, Ticket $ticket): Response
    {
        if ($ticket->user_id !== $request->user()->id) {
            abort(403);
        }

        $ticket->load([
            'replies' => function ($q) {
                $q->with('user', 'attachments')->orderBy('created_at', 'asc');
            },
            'attachments',
        ]);

        return Inertia::render('Client/Tickets/Show', [
            'ticket' => $ticket,
        ]);
    }

    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($ticket->status === 'close') {
            return back()->withErrors(['message' => 'Ticket sudah ditutup. Tidak bisa menambah balasan.']);
        }

        $validated = $request->validate([
            'message' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,mp4,mov,avi',
        ]);

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('tickets/' . $ticket->id, 'public');
                Attachment::create([
                    'attachable_id' => $reply->id,
                    'attachable_type' => TicketReply::class,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    public function close(Ticket $ticket): RedirectResponse
    {
        if ($ticket->user_id !== request()->user()->id) {
            abort(403);
        }

        if ($ticket->status !== 'solved') {
            return back()->withErrors(['message' => 'Hanya ticket dengan status "solved" yang bisa ditutup.']);
        }

        $ticket->update(['status' => 'close']);

        // Notify staff
        $staffUsers = User::whereIn('role', ['admin', 'it-ops'])->get();
        foreach ($staffUsers as $staff) {
            Mail::to($staff->email)->queue(
                new TicketStatusChangedMail($ticket, 'solved', 'close')
            );
        }

        return back()->with('success', 'Ticket berhasil ditutup.');
    }
}
