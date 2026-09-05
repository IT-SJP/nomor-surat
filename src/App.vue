<template>
  <div
    class="min-h-screen flex flex-col bg-gradient-to-b from-slate-50 via-teal-50/20 to-slate-100 text-slate-800">
    <!-- Main Content Area -->
    <main class="flex-1">
      <AnnouncementHero />
      <GuideSteps />
    </main>
  </div>
</template>

<script setup>
import AnnouncementHero from "./components/AnnouncementHero.vue";
import GuideSteps from "./components/GuideSteps.vue";
</script>
