<script setup lang="ts">
import SiteLayout from '@/Layouts/SiteLayout.vue';
import HeroSection from '@/Components/HeroSection.vue';
import TrustBar from '@/Components/TrustBar.vue';
import StatGrid from '@/Components/StatGrid.vue';
import PersonaCards from '@/Components/PersonaCards.vue';
import FeatureGrid from '@/Components/FeatureGrid.vue';
import HowItWorks from '@/Components/HowItWorks.vue';
import PricingTable from '@/Components/PricingTable.vue';
import TestimonialSection from '@/Components/TestimonialSection.vue';
import FaqSection from '@/Components/FaqSection.vue';
import CtaSection from '@/Components/CtaSection.vue';
</script>

<template>
    <SiteLayout title="e-Koperasi - Platform Digital Koperasi Indonesia">
        <HeroSection />
        <TrustBar />
        <StatGrid />
        <PersonaCards />
        <FeatureGrid />
        <HowItWorks />
        <PricingTable />
        <TestimonialSection />
        <FaqSection />
        <CtaSection />
    </SiteLayout>
</template>
