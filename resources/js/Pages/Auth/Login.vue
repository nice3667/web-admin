<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'
import { mdiAccount, mdiAsterisk, mdiEye, mdiEyeOff } from '@mdi/js'
import LayoutGuest from '@/Layouts/Admin/LayoutGuest.vue'
import SectionFullScreen from '@/Components/SectionFullScreen.vue'
import CardBox from '@/Components/CardBox.vue'
import FormCheckRadioGroup from '@/Components/FormCheckRadioGroup.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseDivider from '@/Components/BaseDivider.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import FormValidationErrors from '@/Components/FormValidationErrors.vue'
import NotificationBarInCard from '@/Components/NotificationBarInCard.vue'
import BaseLevel from '@/Components/BaseLevel.vue'
import { ref } from 'vue'

const props = defineProps({
  canResetPassword: Boolean,
  status: {
    type: String,
    default: null
  }
})

const form = useForm({
  email: '',
  password: '',
  remember: []
})

const showPassword = ref(false)

const submit = () => {
  form
    .transform(data => ({
      ... data,
      remember: form.remember && form.remember.length ? 'on' : ''
    }))
    .post(route('login'), {
      onFinish: () => form.reset('password'),
    })
}
</script>

<template>
  <LayoutGuest>
    <Head title="Login" />

    <!-- Beautiful animated background -->
    <div class="min-h-screen flex items-center justify-center relative overflow-hidden p-4">
      <!-- Animated gradient background -->
      <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 animate-gradient"></div>

      <!-- Floating orbs for visual effect -->
      <div class="absolute top-1/4 left-1/4 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob"></div>
      <div class="absolute top-1/3 right-1/4 w-72 h-72 bg-yellow-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000"></div>
      <div class="absolute bottom-1/4 left-1/3 w-72 h-72 bg-pink-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-4000"></div>

      <!-- Subtle grid pattern overlay -->
      <div class="absolute inset-0 opacity-30">
        <div class="w-full h-full" style="background-image: radial-gradient(circle at 30px 30px, rgba(255,255,255,0.05) 1px, transparent 1px); background-size: 60px 60px;"></div>
      </div>

      <div class="w-full max-w-md relative z-10">
        <!-- Logo Section with enhanced styling -->
        <div class="text-center mb-8">
          <div class="inline-flex items-center justify-center w-24 h-24 bg-white/10 backdrop-blur-sm rounded-full shadow-2xl mb-6 border border-white/20">
            <svg class="w-14 h-14 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
            </svg>
          </div>
          <h1 class="text-4xl font-bold text-white mb-3 drop-shadow-2xl bg-gradient-to-r from-white to-gray-200 bg-clip-text text-transparent">Admin Dashboard</h1>
          <p class="text-gray-200 font-medium drop-shadow-lg text-lg">Sign in to your account</p>
        </div>

        <!-- Login Card with glassmorphism effect -->
        <CardBox class="bg-white/10 backdrop-blur-md border border-white/20 shadow-2xl">
          <FormValidationErrors />

          <NotificationBarInCard
            v-if="status"
            color="info"
            class="mb-6"
          >
            {{ status }}
          </NotificationBarInCard>

          <form @submit.prevent="submit" class="space-y-6">
            <!-- Email Field -->
            <div>
              <label for="email" class="block text-sm font-semibold text-white mb-2">
                Email Address
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                  </svg>
                </div>
                <input
                  v-model="form.email"
                  id="email"
                  type="email"
                  autocomplete="email"
                  required
                  class="block w-full pl-10 pr-3 py-3 bg-[#2d174d]/80 border-2 border-purple-400/30 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-300 transition-all duration-300 text-white placeholder-purple-200 backdrop-blur-sm"
                  :class="{ 'border-red-400 focus:ring-red-400 focus:border-red-400': form.errors.email }"
                  placeholder="Enter your email"
                />
              </div>
              <p v-if="form.errors.email" class="mt-1 text-sm text-red-300 font-medium">{{ form.errors.email }}</p>
            </div>

            <!-- Password Field -->
            <div>
              <label for="password" class="block text-sm font-semibold text-white mb-2">
                Password
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  id="password"
                  autocomplete="current-password"
                  required
                  class="block w-full pl-10 pr-12 py-3 bg-[#2d174d]/80 border-2 border-purple-400/30 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-300 transition-all duration-300 text-white placeholder-purple-200 backdrop-blur-sm"
                  :class="{ 'border-red-400 focus:ring-red-400 focus:border-red-400': form.errors.password }"
                  placeholder="Enter your password"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center"
                >
                  <svg v-if="showPassword" class="h-5 w-5 text-gray-300 hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                  </svg>
                  <svg v-else class="h-5 w-5 text-gray-300 hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
              <p v-if="form.errors.password" class="mt-1 text-sm text-red-300 font-medium">{{ form.errors.password }}</p>
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <input
                  v-model="form.remember"
                  id="remember"
                  type="checkbox"
                  class="h-4 w-4 text-purple-400 focus:ring-purple-400 border-white/30 rounded bg-white/10"
                />
                <label for="remember" class="ml-2 block text-sm font-semibold text-white">
                  Remember me
                </label>
              </div>
              <div v-if="canResetPassword" class="text-sm">
                <Link
                  :href="route('password.request')"
                  class="font-semibold text-purple-300 hover:text-purple-200 transition-colors duration-200"
                >
                  Forgot password?
                </Link>
              </div>
            </div>

            <!-- Login Button -->
            <div>
              <button
                type="submit"
                :disabled="form.processing"
                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-purple-600 via-purple-500 to-indigo-600 hover:from-purple-700 hover:via-purple-600 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-[1.02]"
              >
                <span v-if="form.processing" class="absolute left-0 inset-y-0 flex items-center pl-3">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ form.processing ? 'Signing in...' : 'Sign in' }}
              </button>
            </div>

            <!-- Register Link -->
            <div class="text-center">
              <p class="text-sm text-gray-200 font-semibold">
                Don't have an account?
                <Link
                  :href="route('register')"
                  class="font-semibold text-purple-300 hover:text-purple-200 transition-colors duration-200"
                >
                  Sign up
                </Link>
              </p>
            </div>
          </form>
        </CardBox>

        <!-- Footer -->
        <div class="text-center mt-8">
          <p class="text-sm text-gray-300 font-medium drop-shadow-lg">
            © 2024 Admin Dashboard. All rights reserved.
          </p>
        </div>
      </div>
    </div>
  </LayoutGuest>
</template>

<style scoped>
@keyframes gradient {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

@keyframes blob {
  0% { transform: translate(0px, 0px) scale(1); }
  33% { transform: translate(30px, -50px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.9); }
  100% { transform: translate(0px, 0px) scale(1); }
}

.animate-gradient {
  background-size: 400% 400%;
  animation: gradient 15s ease infinite;
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}

input:-webkit-autofill,
input:-webkit-autofill:focus,
input:-webkit-autofill:hover,
input:-webkit-autofill:active {
  -webkit-box-shadow: 0 0 0 1000px #2d174dcc inset !important;
  box-shadow: 0 0 0 1000px #2d174dcc inset !important;
  -webkit-text-fill-color: #fff !important;
  caret-color: #fff !important;
  border-color: #a78bfa !important;
  transition: background-color 5000s ease-in-out 0s;
}

input,
input:focus,
input:active,
input:hover {
  background-color: #2d174dcc !important;
  border-color: #a78bfa4d !important;
  color: #fff !important;
  transition: background-color 0.2s, border-color 0.2s;
}
input::placeholder {
  color: #c4b5fd !important;
  opacity: 1;
}
input::selection {
  background: #a78bfa99;
  color: #fff;
}
</style>
