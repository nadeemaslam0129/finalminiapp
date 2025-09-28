import { reactive } from 'vue';

export const store = reactive({
  user: {
    id: null,
    name: '',
    balance: '0.00'
  },
  transactions: [],
  pagination: {
    page: 1,
    perPage: 20,
    lastPage: 1
  },
  loading: {
    transactions: false,
    transfer: false
  }
});
