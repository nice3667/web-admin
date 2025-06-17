<script setup>
import { computed } from 'vue'

const props = defineProps({
  username: {
    type: String,
    default: 'user'
  },
  avatar: {
    type: String,
    default: null
  },
  api: {
    type: String,
    default: 'avataaars'
  }
})

const avatar = computed(() => {
  const safeUsername = props.username || 'user'
  return props.avatar ?? `https://api.dicebear.com/7.x/${props.api}/svg?seed=${safeUsername.replace(/[^a-z0-9]+/i, '-')}`
})

const username = computed(() => props.username || 'User')
</script>

<template>
  <div>
    <img
      :src="avatar"
      :alt="username"
      class="rounded-full block h-auto w-full max-w-full bg-gray-100 dark:bg-slate-800"
    >
  </div>
</template>
