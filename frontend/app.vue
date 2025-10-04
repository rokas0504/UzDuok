<template>
  <PlusModalNuxtPage v-slot="{ route }">
    <NuxtLayout>
      <NuxtPage :route="route" />
    </NuxtLayout>
  </PlusModalNuxtPage>
  <PlusModalPage name="modal" />
</template>
