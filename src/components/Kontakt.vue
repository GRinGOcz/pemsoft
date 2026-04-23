<template>
  <form @submit.prevent="odeslat" class="space-y-4">
    <div>
      <label class="block text-sm text-slate-900 dark:text-slate-400 mb-1">Váš e-mail</label>
      <input 
        v-model="email" 
        type="email" 
        class="w-full dark:bg-slate-800 border dark:border-slate-700 rounded-lg p-3 focus:border-blue-500 outline-none"
        placeholder="email@priklad.cz"
      />
    </div>
    <div>
      <label class="block text-sm text-slate-900 dark:text-slate-400 mb-1">Zpráva</label>
      <textarea 
        v-model="zprava" 
        rows="4" 
        class="w-full dark:bg-slate-800 border dark:border-slate-700 rounded-lg p-3 focus:border-blue-500 outline-none"
      ></textarea>
    </div>
    <button 
      type="submit" 
      :disabled="stav === 'posilam'"
      class="w-full bg-[#BCD8C1]/70 hover:bg-[#BCD8C1] font-bold py-3 px-8 rounded-lg transition border border-slate-500 disabled:opacity-50"
    >
      {{ stav === 'posilam' ? 'Odesílám...' : 'Odeslat poptávku' }}
    </button>
    <p v-if="stav === 'hotovo'" class="text-emerald-400 text-center mt-2">Zpráva byla úspěšně odeslána!</p>
  </form>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const email = ref('');
const zprava = ref('');
const stav = ref<'nic' | 'posilam' | 'hotovo'>('nic');

const odeslat = async () => {
  if (!email.value || !zprava.value) return; // Drobná pojistka
  
  stav.value = 'posilam';
  
  try {
    const response = await fetch('/poslat.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json' // TOTO JE KLÍČOVÉ
      },
      body: JSON.stringify({ 
        email: email.value, 
        zprava: zprava.value 
      })
    });

    if (response.ok) {
      stav.value = 'hotovo';
      email.value = ''; // Vyčistit pole po úspěchu
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