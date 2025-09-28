<template>
  <form @submit.prevent="submitTransfer" class="transfer-form">
    <input type="number" v-model.number="receiver_id" placeholder="Receiver ID" required />
    <input type="number" v-model.number="amount" placeholder="Amount" required min="0.01" step="0.01" />
    <button type="submit">Send</button>
  </form>
</template>

<script>
import { ref } from 'vue';

export default {
  name: 'TransferForm',
  emits: ['transfer'],
  setup(_, { emit }) {
    const receiver_id = ref('');
    const amount = ref('');

    const submitTransfer = () => {
      emit('transfer', { receiver_id: Number(receiver_id.value), amount: Number(amount.value) });
      receiver_id.value = '';
      amount.value = '';
    };

    return { receiver_id, amount, submitTransfer };
  },
};
</script>

<style scoped>
.transfer-form {
  display: flex;
  flex-direction: column;
}

.transfer-form input {
  margin-bottom: 10px;
  padding: 10px;
  border-radius: 6px;
  border: 1px solid #ccc;
}

.transfer-form button {
  padding: 12px;
  border: none;
  background: #2ecc71;
  color: #fff;
  font-weight: 600;
  border-radius: 6px;
  cursor: pointer;
}

.transfer-form button:hover {
  background: #27ae60;
}
</style>
