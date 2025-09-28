<template>
  <div class="dashboard">
    <!-- Header -->
    <header class="dashboard-header">
      <h1>Welcome, {{ userName }}</h1>
      <p class="subtitle">Balance: {{ Number(balance).toFixed(2) }}</p>

      <!-- Notification Bell -->
      <div class="notification-bell" @click="toggleNotifications">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
          <path d="M8 16a2 2 0 0 0 1.985-1.75H6.015A2 2 0 0 0 8 16zm.104-14a1 1 0 0 1 .896.553l.832 1.67A5.002 5.002 0 0 1 13 9v3l.993 1H2.007L3 12V9a5.002 5.002 0 0 1 3.168-4.777l.832-1.67a1 1 0 0 1 .896-.553h.208z"/>
        </svg>
        <span v-if="unreadCount" class="badge">{{ unreadCount }}</span>

        <div v-if="showNotifications" class="notification-dropdown">
          <h4>Notifications</h4>
          <ul v-if="notifications.length">
            <li v-for="(n, i) in notifications" :key="i">
              {{ n.message }}
              <span class="timestamp">{{ n.timestamp }}</span>
            </li>
          </ul>
          <p v-else class="no-notifications">No notifications</p>
        </div>
      </div>
    </header>

    <!-- Transfer Form -->
    <section class="transfer-section card">
      <h2>Transfer Funds</h2>
      <form @submit.prevent="sendTransfer">
        <label>Receiver</label>
        <select v-model="transfer.receiver" required>
          <option value="" disabled>Select user</option>
          <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
        </select>

        <label>Amount</label>
        <input type="number" step="0.01" v-model="transfer.amount" placeholder="Amount" required />

        <button type="submit">Send</button>
      </form>
      <p v-if="error" class="error">{{ error }}</p>
      <p v-if="success" class="success">{{ success }}</p>
    </section>

    <!-- Transaction History -->
    <section class="transactions-section card">
      <h2>Transaction History</h2>
      <table>
        <thead>
          <tr>
            <th>Type</th>
            <th>Amount</th>
            <th>Sender</th>
            <th>Receiver</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="tx in transactions" :key="tx.id">
            <td>{{ tx.sender_id === userId ? 'Sent' : 'Received' }}</td>
            <td>{{ Number(tx.amount).toFixed(2) }}</td>
            <td>{{ tx.sender_name }}</td>
            <td>{{ tx.receiver_name }}</td>
            <td>{{ new Date(tx.created_at).toLocaleString() }}</td>
          </tr>
        </tbody>
      </table>
    </section>
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue';
import axios from 'axios';
import { store } from '../store';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

export default {
  setup() {
    const userId = store.user.id;
    const userName = store.user.name;
    const balance = ref(Number(store.user.balance) || 0);
    const transactions = ref([]);
    const notifications = ref([]);
    const showNotifications = ref(false);

    const transfer = reactive({ receiver: '', amount: 0 });
    const users = ref([]);
    const error = ref('');
    const success = ref('');

    axios.defaults.baseURL = import.meta.env.VITE_API_BASE_URL;
    axios.defaults.withCredentials = true;

    const unreadCount = computed(() => notifications.value.filter(n => !n.read).length);

    // Fetch users
    const fetchUsers = async () => {
      try {
        const res = await axios.get('/users');
        users.value = res.data.filter(u => u.id !== userId);
      } catch (err) {
        console.error('Error fetching users:', err);
      }
    };

    // Fetch transactions
    const fetchTransactions = async () => {
      try {
        const res = await axios.get('/transactions');
        transactions.value = res.data.transactions.map(tx => ({
          ...tx,
          sender_name: tx.sender_name || (tx.sender_id === userId ? 'You' : 'User ' + tx.sender_id),
          receiver_name: tx.receiver_name || (tx.receiver_id === userId ? 'You' : 'User ' + tx.receiver_id),
        }));
        balance.value = Number(res.data.balance) || 0;
      } catch (err) {
        console.error(err);
      }
    };

    // Fetch notifications
    const fetchNotifications = async () => {
      try {
        const res = await axios.get('/notifications');
        notifications.value = res.data.map(n => ({
          message: n.message,
          timestamp: new Date(n.created_at).toLocaleTimeString(),
          read: n.read
        }));
      } catch (err) {
        console.error('Error fetching notifications:', err);
      }
    };

    const sendTransfer = async () => {
      error.value = '';
      success.value = '';
      try {
        await axios.post('/transactions', {
          receiver_id: transfer.receiver,
          amount: parseFloat(transfer.amount),
        });
        transfer.receiver = '';
        transfer.amount = 0;
        success.value = 'Transfer successful';
        fetchTransactions();
      } catch (err) {
        error.value = err.response?.data?.message || 'Transfer failed';
      }
    };

    const toggleNotifications = () => {
      showNotifications.value = !showNotifications.value;
      if (showNotifications.value) {
        notifications.value = notifications.value.map(n => ({ ...n, read: true }));
      }
    };

    onMounted(() => {
      fetchUsers();
      fetchTransactions();
      fetchNotifications();

      window.Pusher = Pusher;
      const echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_KEY,
        cluster: import.meta.env.VITE_PUSHER_CLUSTER,
        forceTLS: true,
        authEndpoint: `${import.meta.env.VITE_API_BASE_URL}/broadcasting/auth`,
        withCredentials: true,
      });

      echo.private(`user.${userId}`)
        .listen('TransferCompleted', (e) => {
          notifications.value.unshift({
            message: e.message,
            timestamp: new Date().toLocaleTimeString(),
            read: false,
          });

          const tx = e.transaction;
          transactions.value.unshift({
            ...tx,
            sender_name: e.sender_name,
            receiver_name: e.receiver_name,
          });

          balance.value = e.transaction.sender_id === userId ? e.sender_balance : e.receiver_balance;
        });
    });

    return {
      userName,
      balance,
      transactions,
      notifications,
      showNotifications,
      toggleNotifications,
      transfer,
      sendTransfer,
      users,
      error,
      success,
      userId,
      unreadCount,
    };
  },
};
</script>

<style scoped>
.dashboard {
  max-width: 900px;
  margin: 50px auto;
  font-family: 'Poppins', sans-serif;
}
.dashboard-header {
  text-align: center;
  margin-bottom: 30px;
  position: relative;
}
.dashboard-header h1 {
  font-size: 2.2rem;
  font-weight: 700;
  color: #2c3e50;
}
.dashboard-header .subtitle {
  font-size: 1.2rem;
  color: #34495e;
  margin-top: 8px;
}
.notification-bell {
  position: absolute;
  top: 0;
  right: 0;
  cursor: pointer;
}
.notification-bell svg {
  width: 32px;
  height: 32px;
  color: #2c3e50;
}
.notification-bell .badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #e74c3c;
  color: #fff;
  border-radius: 50%;
  padding: 3px 7px;
  font-size: 0.75rem;
}
.notification-dropdown {
  position: absolute;
  top: 40px;
  right: 0;
  background: #fff;
  width: 300px;
  max-height: 400px;
  overflow-y: auto;
  box-shadow: 0 10px 25px rgba(0,0,0,0.15);
  border-radius: 10px;
  z-index: 100;
  padding: 10px;
}
.notification-dropdown h4 {
  margin-top: 0;
  font-size: 1.2rem;
  color: #34495e;
  border-bottom: 1px solid #ddd;
  padding-bottom: 5px;
}
.notification-dropdown ul {
  list-style: none;
  padding-left: 0;
}
.notification-dropdown li {
  padding: 8px;
  border-bottom: 1px solid #f1f1f1;
  font-weight: 500;
}
.notification-dropdown .timestamp {
  font-size: 0.75rem;
  color: #7f8c8d;
  display: block;
  margin-top: 2px;
}
.notification-dropdown .no-notifications {
  text-align: center;
  color: #7f8c8d;
  font-style: italic;
  padding: 20px;
}
form label {
  display: block;
  margin-bottom: 5px;
  font-weight: 600;
}
input, select {
  display: block;
  width: 100%;
  margin-bottom: 10px;
  padding: 10px;
  border-radius: 6px;
  border: 1px solid #ccc;
}
button {
  padding: 10px 15px;
  background: #2ecc71;
  border: none;
  color: #fff;
  border-radius: 6px;
  cursor: pointer;
}
button:hover {
  background: #27ae60;
}
.error {
  color: #e74c3c;
  font-weight: 600;
  margin-top: 10px;
}
.success {
  color: #2ecc71;
  font-weight: 600;
  margin-top: 10px;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}
thead th {
  background: #ecf0f1;
  padding: 12px;
  text-align: left;
}
tbody td {
  padding: 10px;
  border-bottom: 1px solid #ddd;
}
tbody tr:nth-child(even) {
  background: #f9f9f9;
}
</style>
