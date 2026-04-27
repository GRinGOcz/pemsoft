<template>
  <div class="flex lg:flex-row flex-col justify-center gap-2 w-full mt-16">
    <div class="rounded-3xl border border-slate-500 bg-[#439A86]/90 p-8 text-left shadow-xl/20 lg:basis-lg mx-6 grid grid-cols-1 justify-center">
      <h2 class="text-3xl font-bold mb-2">Petr Meca</h2>
      <p class="text-lg font-bold">Adresa: <span class="text-lg font-normal mb-2 text-left">Milíkov 192, 73981 Milíkov</span></p>
      <p class="text-lg font-bold">IČ: <span class="text-lg font-normal mb-2 text-left">23851678</span></p>
      <p class="text-lg font-bold">E-mail: <span class="text-lg font-normal mb-2 text-left">petrmeca2@gmail.com</span></p>
      <p class="text-lg font-bold">Mobil: <span class="text-lg font-normal mb-2 text-left">+420 792 377 885</span></p>
    </div>
    <form @submit.prevent="odeslat" class="space-y-4 basis-lg">
      <div>
        <label class="block text-lg mb-1">Váš e-mail</label>
        <input 
          v-model="email" 
          type="email" 
          class="w-full border border-slate-700 rounded-lg p-3 focus:border-blue-500 outline-none shadow-xl/20"
          placeholder="email@priklad.cz"
        />
      </div>
      <div>
        <label class="block text-lg mb-1">Zpráva</label>
        <textarea 
          v-model="zprava" 
          rows="4" 
          class="w-full border border-slate-700 rounded-lg p-3 focus:border-blue-500 outline-none shadow-xl/20"
        ></textarea>
      </div>
      <button 
        type="submit" 
        :disabled="stav === 'posilam'"
        class="w-full text-[#222E50] bg-[#a8c9ae] hover:bg-[#BCD8C1] font-bold py-3 px-8 rounded-lg transition border border-slate-500 disabled:opacity-50"
      >
        {{ stav === 'posilam' ? 'Odesílám...' : 'Odeslat poptávku' }}
      </button>
      <p v-if="stav === 'hotovo'" class="text-emerald-400 text-center mt-2">Zpráva byla úspěšně odeslána!</p>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const email = ref('');
const zprava = ref('');
const stav = ref<'nic' | 'posilam' | 'hotovo'>('nic');

const odeslat = async () => {
  if (!email.value || !zprava.value) return;
  
  stav.value = 'posilam';
  
  try {
    const response = await fetch('/poslat.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ 
        email: email.value, 
        zprava: zprava.value 
      })
    });

    if (response.ok) {
      stav.value = 'hotovo';
      email.value = '';
      zprava.value = '';
    } else {
      stav.value = 'nic';
      alert('Chyba při odesílání. Zkuste to prosím znovu.');
    }
  } catch (error) {
    stav.value = 'nic';
    console.error('Chyba:', error);
  }
};
</script>