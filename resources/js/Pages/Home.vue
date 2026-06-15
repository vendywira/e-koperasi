<script setup lang="ts">
import SiteLayout from '@/Layouts/SiteLayout.vue';
import HeroSection from '@/Components/HeroSection.vue';
import TrustBar from '@/Components/TrustBar.vue';
import ProductShowcase from '@/Components/ProductShowcase.vue';
import AppFeature from '@/Components/AppFeature.vue';
import WhyChooseUs from '@/Components/WhyChooseUs.vue';
import PersonaCards from '@/Components/PersonaCards.vue';
import FeatureGrid from '@/Components/FeatureGrid.vue';
import HowItWorks from '@/Components/HowItWorks.vue';
import PricingTable from '@/Components/PricingTable.vue';
import TestimonialSection from '@/Components/TestimonialSection.vue';
import FaqSection from '@/Components/FaqSection.vue';
import CtaSection from '@/Components/CtaSection.vue';

defineProps<{
    testimonials?: Array<any>;
    faqs?: Array<{ question: string; answer: string }>;
    features?: Array<any>;
    personas?: Array<any>;
    howItWorks?: Array<any>;
    trust?: Array<{ name: string; logo?: string }>;
}>();
</script>

<template>
    <SiteLayout
        title="e-Koperasi - Platform Digital Koperasi Indonesia"
        description="Kelola simpan pinjam, simpanan, dan laporan keuangan koperasi Anda dengan mudah, aman, dan modern."
    >
        <HeroSection />
        <TrustBar :items="trust" />
        <ProductShowcase />
        <AppFeature />
        <FeatureGrid :items="features" />
        <WhyChooseUs />
        <PersonaCards :items="personas" />
        <HowItWorks :items="howItWorks" />
        <PricingTable />
        <TestimonialSection :items="testimonials" />
        <FaqSection :items="faqs" />
        <CtaSection />
    </SiteLayout>
</template>
