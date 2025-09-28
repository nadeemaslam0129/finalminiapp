<template>
  <div class="transaction-list">
    <div v-if="!transactions.length">No transactions yet</div>
    <ul>
      <li v-for="t in transactions" :key="t.id">
        <strong>{{ t.type }}:</strong> ${{ t.amount.toFixed(2) }}
        <span v-if="t.recipient">to {{ t.recipient }}</span>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'TransactionList',
  props: {
    transactions: { type: Array, default: () => [] },
  },
};
</script>

<style scoped>
.transaction-list {
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.05);
}

.transaction-list ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.transaction-list li {
  padding: 10px 0;
  border-bottom: 1px solid #eee;
}
</style>
