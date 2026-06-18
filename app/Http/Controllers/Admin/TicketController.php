<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TicketRepliedMail;
use App\Mail\TicketStatusChangedMail;
use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Ticket::query()->with(['user', 'assignedTo']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $staffUsers = User::whereIn('role', ['admin', 'it-ops'])->get(['id', 'name']);

        return Inertia::render('Admin/Tickets/Index', [
            'tickets' => $tickets,
            'filters' => $request->only(['status', 'priority', 'assigned_to', 'search']),
            'staffUsers' => $staffUsers,
        ]);
    }

    public function show(Ticket $ticket): Response
    {
        $ticket->load([
            'user',
            'assignedTo',
            'replies' => function ($q) {
                $q->with('user', 'attachments')->orderBy('created_at', 'asc');
            },
            'attachments',
        ]);

        $staffUsers = User::whereIn('role', ['admin', 'it-ops'])->get(['id', 'name']);

        return Inertia::render('Admin/Tickets/Show', [
            'ticket' => $ticket,
            'staffUsers' => $staffUsers,
        ]);
    }

    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        if ($ticket->status === 'close') {
            return back()->withErrors(['message' => 'Ticket sudah ditutup.']);
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

        // Send email to client when staff replies
        Mail::to($ticket->user->email)->queue(
            new TicketRepliedMail($reply)
        );

        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        if ($ticket->status === 'close') {
            return back()->withErrors(['message' => 'Ticket sudah ditutup. Tidak bisa mengubah status.']);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,acknowledge,in_progress,solved,close',
        ]);

        $validTransitions = [
            'pending' => ['acknowledge'],
            'acknowledge' => ['in_progress', 'pending'],
            'in_progress' => ['solved', 'acknowledge', 'pending'],
            'solved' => ['close', 'in_progress'],
            'close' => [],
        ];

        $allowed = $validTransitions[$ticket->status] ?? [];
        if (!in_array($validated['status'], $allowed)) {
            return back()->withErrors([
                'status' => 'Tidak bisa mengubah status dari "' . $ticket->status . '" ke "' . $validated['status'] . '".'
            ]);
        }

        $oldStatus = $ticket->status;
        $ticket->update(['status' => $validated['status']]);

        // Send email to client
        Mail::to($ticket->user->email)->queue(
            new TicketStatusChangedMail($ticket, $oldStatus, $validated['status'])
        );

        return back()->with('success', 'Status ticket berhasil diperbarui.');
    }

    public function assign(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $ticket->update(['assigned_to' => $validated['assigned_to']]);

        return back()->with('success', 'Ticket berhasil di-assign.');
    }

    public function destroyAttachment(Ticket $ticket, Attachment $attachment): RedirectResponse
    {
        if ($attachment->attachable_id !== $ticket->id && $attachment->attachable_type !== Ticket::class) {
            $replyIds = $ticket->replies()->pluck('id')->toArray();
            if (!in_array($attachment->attachable_id, $replyIds)) {
                abort(403);
            }
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }
}
