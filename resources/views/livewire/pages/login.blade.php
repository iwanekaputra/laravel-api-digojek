<div class="min-h-screen bg-slate-900 flex items-center justify-center p-4 sm:p-6">
  <div class="w-full max-w-md">
    <!-- Logo Section -->
    <div class="flex justify-center mb-8">
      <img class="h-12 w-auto object-contain" src="/images/logo.png" alt="Iconic">
    </div>

    <!-- Card Container -->
    <div class="bg-slate-800 border border-slate-700/60 rounded-2xl shadow-xl p-8 backdrop-blur-sm">
      <!-- Header -->
      <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold tracking-tight text-white">Login to your account</h2>
        <p class="text-sm text-slate-400 mt-1">Masukkan kredensial Anda untuk melanjutkan</p>
      </div>

      <!-- Form -->
      <form class="space-y-5" wire:submit="login">
        <!-- Email Field -->
        <div>
          <label for="signin-email" class="sr-only">Email</label>
          <div class="relative">
            <input
              wire:model="email"
              type="email"
              id="signin-email"
              placeholder="Alamat Email"
              class="w-full px-4 py-3 bg-slate-900/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all duration-200 text-sm">
          </div>
        </div>

        <!-- Password Field -->
        <div>
          <label for="signin-password" class="sr-only">Password</label>
          <div class="relative">
            <input
              wire:model="password"
              type="password"
              id="signin-password"
              placeholder="Kata Sandi"
              class="w-full px-4 py-3 bg-slate-900/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all duration-200 text-sm">
          </div>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          class="w-full py-3 px-4 bg-cyan-500 hover:bg-cyan-400 active:scale-[0.98] text-slate-950 font-semibold rounded-xl shadow-lg shadow-cyan-500/20 transition-all duration-200 text-sm uppercase tracking-wider">
          Login
        </button>
      </form>
    </div>
  </div>
</div>