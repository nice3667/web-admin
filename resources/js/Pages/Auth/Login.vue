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
import { ref, onMounted } from 'vue'

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
const isLoading = ref(false)
const mounted = ref(false)

onMounted(() => {
  mounted.value = true
})

const submit = () => {
  isLoading.value = true
  form
    .transform(data => ({
      ... data,
      remember: form.remember && form.remember.length ? 'on' : ''
    }))
    .post(route('login'), {
      onFinish: () => {
        form.reset('password')
        isLoading.value = false
      },
    })
}
</script>

<template>
  <LayoutGuest>
    <Head title="Admin Login" />

    <div class="min-h-screen relative overflow-hidden">
      <!-- Professional Background with Geometric Patterns -->
      <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-blue-900/30 via-transparent to-transparent"></div>
      </div>

      <!-- Subtle Geometric Grid -->
      <div class="absolute inset-0 opacity-10">
        <div class="w-full h-full animate-grid-subtle"
             style="background-image: linear-gradient(rgba(59, 130, 246, 0.3) 1px, transparent 1px), linear-gradient(90deg, rgba(59, 130, 246, 0.3) 1px, transparent 1px); background-size: 60px 60px;"></div>
      </div>

      <!-- Professional Floating Elements -->
      <div class="absolute inset-0 opacity-20">
        <div class="absolute top-1/4 left-1/4 w-80 h-80 bg-gradient-to-r from-blue-600 to-blue-400 rounded-full mix-blend-multiply filter blur-3xl animate-pulse-professional"></div>
        <div class="absolute top-1/3 right-1/4 w-64 h-64 bg-gradient-to-r from-slate-600 to-slate-400 rounded-full mix-blend-multiply filter blur-3xl animate-pulse-professional animation-delay-3000"></div>
        <div class="absolute bottom-1/4 left-1/3 w-72 h-72 bg-gradient-to-r from-blue-500 to-slate-500 rounded-full mix-blend-multiply filter blur-3xl animate-pulse-professional animation-delay-6000"></div>
      </div>

      <!-- Animated Dots -->
      <div class="absolute inset-0 overflow-hidden">
        <div v-for="i in 30" :key="i"
             class="absolute animate-float-subtle opacity-20"
             :style="{
               left: Math.random() * 100 + '%',
               top: Math.random() * 100 + '%',
               animationDelay: Math.random() * 15 + 's',
               animationDuration: (Math.random() * 8 + 12) + 's'
             }">
          <div class="w-1 h-1 bg-blue-400 rounded-full"></div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="relative z-10 min-h-screen flex items-center justify-center p-3 sm:p-4 lg:p-6">
        <div class="w-full max-w-sm sm:max-w-md lg:max-w-lg">
          <!-- Professional Logo Section -->
          <div class="text-center mb-6 sm:mb-8 lg:mb-10" :class="{ 'animate-fade-in-up': mounted }">
            <div class="relative inline-block mb-4 sm:mb-6">
              <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-blue-400 rounded-xl blur-lg opacity-40 animate-pulse-slow"></div>
              <div class="relative bg-white/5 backdrop-blur-xl rounded-xl p-4 sm:p-5 lg:p-6 border border-white/10 shadow-2xl">
                <svg class="w-10 h-10 sm:w-12 sm:h-12 lg:w-14 lg:h-14 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                </svg>
              </div>
            </div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-2 sm:mb-3 text-white drop-shadow-lg">
              Admin Portal
            </h1>
            <p class="text-blue-200 text-sm sm:text-base lg:text-lg font-medium">Secure Access Dashboard</p>
          </div>

          <!-- Professional Login Card -->
          <div class="relative" :class="{ 'animate-fade-in-up animation-delay-300': mounted }">
            <!-- Card Subtle Glow -->
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 to-blue-400/10 rounded-xl blur-xl"></div>

            <CardBox class="relative bg-white/[0.05] backdrop-blur-xl border border-white/10 shadow-xl rounded-xl lg:rounded-2xl overflow-hidden">
              <!-- Professional Header Line -->
              <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-blue-600 to-blue-400"></div>

              <div class="p-4 sm:p-6 lg:p-8">
                <FormValidationErrors />

                <NotificationBarInCard
                  v-if="status"
                  color="info"
                  class="mb-6"
                >
                  {{ status }}
                </NotificationBarInCard>

                <form @submit.prevent="submit" class="space-y-4 sm:space-y-6">
                  <!-- Email Field -->
                  <div class="space-y-2">
                    <label for="email" class="block text-xs sm:text-sm font-semibold text-white/90 mb-2">
                      Email Address
                    </label>
                    <div class="relative group">
                      <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 text-blue-300 group-focus-within:text-blue-200 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                      </div>
                      <input
                        v-model="form.email"
                        id="email"
                        type="email"
                        autocomplete="email"
                        required
                        class="block w-full pl-10 sm:pl-12 pr-3 sm:pr-4 py-3 sm:py-3.5 bg-white/5 backdrop-blur-sm border border-white/20 rounded-lg focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200 text-white placeholder-blue-200/60 shadow-inner hover:bg-white/10 hover:border-white/30 text-sm sm:text-base"
                        :class="{ 'border-red-400/50 focus:ring-red-400/50 focus:border-red-400/50': form.errors.email }"
                        placeholder="Enter your email address"
                      />
                    </div>
                    <p v-if="form.errors.email" class="text-xs sm:text-sm text-red-300 font-medium animate-fade-in">{{ form.errors.email }}</p>
                  </div>

                  <!-- Password Field -->
                  <div class="space-y-2">
                    <label for="password" class="block text-xs sm:text-sm font-semibold text-white/90 mb-2">
                      Password
                    </label>
                    <div class="relative group">
                      <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 text-blue-300 group-focus-within:text-blue-200 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                      </div>
                      <input
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        id="password"
                        autocomplete="current-password"
                        required
                        class="block w-full pl-10 sm:pl-12 pr-12 sm:pr-14 py-3 sm:py-3.5 bg-white/5 backdrop-blur-sm border border-white/20 rounded-lg focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200 text-white placeholder-blue-200/60 shadow-inner hover:bg-white/10 hover:border-white/30 text-sm sm:text-base"
                        :class="{ 'border-red-400/50 focus:ring-red-400/50 focus:border-red-400/50': form.errors.password }"
                        placeholder="Enter your password"
                      />
                      <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3 sm:pr-4 flex items-center transition-transform duration-200 hover:scale-110"
                      >
                        <svg v-if="showPassword" class="h-4 w-4 sm:h-5 sm:w-5 text-blue-300 hover:text-white transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                        </svg>
                        <svg v-else class="h-4 w-4 sm:h-5 sm:w-5 text-blue-300 hover:text-white transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </button>
                    </div>
                    <p v-if="form.errors.password" class="text-xs sm:text-sm text-red-300 font-medium animate-fade-in">{{ form.errors.password }}</p>
                  </div>

                  <!-- Remember Me & Forgot Password -->
                  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pt-2 gap-3 sm:gap-0">
                    <label class="flex items-center group cursor-pointer">
                      <div class="relative">
                        <input
                          v-model="form.remember"
                          id="remember"
                          type="checkbox"
                          class="sr-only"
                        />
                        <div class="w-4 h-4 sm:w-5 sm:h-5 bg-white/5 border border-white/30 rounded group-hover:border-blue-400/50 transition-all duration-200"
                             :class="{ 'bg-blue-600 border-blue-600': form.remember.length }">
                          <svg v-if="form.remember.length" class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-white absolute inset-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                          </svg>
                        </div>
                      </div>
                      <span class="ml-2 sm:ml-3 text-xs sm:text-sm font-medium text-white/90 group-hover:text-white transition-colors duration-200">
                        Remember me
                      </span>
                    </label>
                    <div v-if="canResetPassword" class="text-center sm:text-right">
                      <Link
                        :href="route('password.request')"
                        class="text-xs sm:text-sm font-medium text-blue-300 hover:text-blue-200 transition-colors duration-200 hover:underline"
                      >
                        Forgot password?
                      </Link>
                    </div>
                  </div>

                  <!-- Professional Login Button -->
                  <div class="pt-3 sm:pt-4">
                    <div class="relative group">
                      <button
                        type="submit"
                        :disabled="form.processing"
                        class="relative w-full flex justify-center items-center py-3 sm:py-3.5 px-4 sm:px-6 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg shadow-lg hover:shadow-blue-500/20 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-transparent disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-[1.01] active:scale-[0.99] overflow-hidden text-sm sm:text-base"
                      >
                        <!-- Subtle Shimmer Effect -->
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>

                        <!-- Button Content -->
                        <div class="relative z-10 flex items-center">
                          <span v-if="form.processing" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 sm:mr-3 h-4 w-4 sm:h-5 sm:w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm sm:text-base">Authenticating...</span>
                          </span>
                          <span v-else class="flex items-center">
                            <svg class="mr-1.5 sm:mr-2 h-4 w-4 sm:h-5 sm:w-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span class="text-sm sm:text-base font-semibold">Sign In</span>
                          </span>
                        </div>
                      </button>
                    </div>
                  </div>

                  <!-- Register Link -->
                  <div class="text-center pt-3 sm:pt-4 border-t border-white/10">
                    <p class="text-xs sm:text-sm text-slate-300">
                      Don't have an account?
                      <Link
                        :href="route('register')"
                        class="font-semibold text-blue-300 hover:text-blue-200 transition-colors duration-200 hover:underline ml-1"
                      >
                        Request Access
                      </Link>
                    </p>
                  </div>
                </form>
              </div>
            </CardBox>
          </div>

          <!-- Professional Footer -->
          <div class="text-center mt-6 sm:mt-8" :class="{ 'animate-fade-in-up animation-delay-600': mounted }">
            <p class="text-xs sm:text-sm text-slate-400 font-medium">
              © 2024 Admin Portal. All rights reserved.
            </p>
            <div class="flex flex-col sm:flex-row justify-center items-center gap-2 sm:gap-6 mt-2 sm:mt-3">
              <a href="#" class="text-xs text-slate-500 hover:text-blue-300 transition-colors duration-200 font-medium">Privacy Policy</a>
              <a href="#" class="text-xs text-slate-500 hover:text-blue-300 transition-colors duration-200 font-medium">Terms of Service</a>
              <a href="#" class="text-xs text-slate-500 hover:text-blue-300 transition-colors duration-200 font-medium">Support</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </LayoutGuest>
</template>

<style scoped>
/* Professional Animations */
@keyframes pulse-professional {
  0%, 100% {
    transform: scale(1);
    opacity: 0.2;
  }
  50% {
    transform: scale(1.05);
    opacity: 0.3;
  }
}

@keyframes float-subtle {
  0%, 100% { transform: translateY(0px) translateX(0px); }
  33% { transform: translateY(-15px) translateX(5px); }
  66% { transform: translateY(5px) translateX(-5px); }
}

@keyframes grid-subtle {
  0% { transform: translateX(0) translateY(0); }
  100% { transform: translateX(60px) translateY(60px); }
}

@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fade-in-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pulse-slow {
  0%, 100% {
    opacity: 0.4;
  }
  50% {
    opacity: 0.6;
  }
}

.animate-pulse-professional {
  animation: pulse-professional 8s ease-in-out infinite;
}

.animate-float-subtle {
  animation: float-subtle 12s ease-in-out infinite;
}

.animate-grid-subtle {
  animation: grid-subtle 30s linear infinite;
}

.animate-fade-in {
  animation: fade-in 0.4s ease-out;
}

.animate-fade-in-up {
  animation: fade-in-up 0.6s ease-out;
}

.animate-pulse-slow {
  animation: pulse-slow 4s ease-in-out infinite;
}

.animation-delay-300 {
  animation-delay: 0.3s;
}

.animation-delay-600 {
  animation-delay: 0.6s;
}

.animation-delay-3000 {
  animation-delay: 3s;
}

.animation-delay-6000 {
  animation-delay: 6s;
}

/* Professional Input Styling */
input:-webkit-autofill,
input:-webkit-autofill:focus,
input:-webkit-autofill:hover,
input:-webkit-autofill:active {
  -webkit-box-shadow: 0 0 0 1000px rgba(255, 255, 255, 0.05) inset !important;
  box-shadow: 0 0 0 1000px rgba(255, 255, 255, 0.05) inset !important;
  -webkit-text-fill-color: #fff !important;
  caret-color: #fff !important;
  border-color: rgba(59, 130, 246, 0.5) !important;
  transition: background-color 5000s ease-in-out 0s;
}

input::placeholder {
  color: rgba(147, 197, 253, 0.6) !important;
  opacity: 1;
}

input::selection {
  background: rgba(59, 130, 246, 0.3);
  color: #fff;
}

/* Enhanced glassmorphism */
.backdrop-blur-xl {
  backdrop-filter: blur(24px);
}

/* Professional scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
}

::-webkit-scrollbar-thumb {
  background: rgba(59, 130, 246, 0.4);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(59, 130, 246, 0.6);
}
</style>
