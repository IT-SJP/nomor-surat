<template>
  <div class="min-h-screen flex flex-col bg-gradient-to-b from-slate-50 via-teal-50/20 to-slate-100 text-slate-800">
    <!-- Navigation Header -->
    <Navbar />

    <!-- Main Content Area -->
    <main class="flex-1">
      <AnnouncementHero />
      <GuideSteps />
    </main>

    <!-- Footer / Helpdesk -->
    <FooterHelp />
  </div>
</template>

<script setup>
import Navbar from './components/Navbar.vue'
import AnnouncementHero from './components/AnnouncementHero.vue'
import GuideSteps from './components/GuideSteps.vue'
import FooterHelp from './components/FooterHelp.vue'
</script>
