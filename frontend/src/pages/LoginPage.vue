<template>
  <div class="login-page">
    <h2>Login</h2>
    <form @submit.prevent="loginUser">
      <input type="email" v-model="email" placeholder="Email" required />
      <input type="password" v-model="password" placeholder="Password" required />
      <button type="submit">Login</button>
    </form>
    <p v-if="error" class="error">{{ error }}</p>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { store } from '../store';

export default {
  setup() {
    const email = ref('');
    const password = ref('');
    const error = ref('');
    const router = useRouter();

    axios.defaults.baseURL = import.meta.env.VITE_API_BASE_URL;
    axios.defaults.withCredentials = true;

    const loginUser = async () => {
      error.value = '';
      try {
        // First get CSRF cookie
        await axios.get('/sanctum/csrf-cookie');

        // Post login request
        await axios.post('/login', { email: email.value, password: password.value });

        // Get user details
        const res = await axios.get('/user');
        store.user.id = res.data.id;
        store.user.name = res.data.name;
        store.user.balance = res.data.balance ?? 0;

        router.push('/dashboard');
      } catch (err) {
        error.value = err.response?.data?.message || 'Login failed';
      }
    };

    return { email, password, error, loginUser };
  },
};
</script>

<style scoped>
.login-page {
  max-width: 400px;
  margin: 100px auto;
  padding: 40px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  font-family: 'Poppins', sans-serif;
  text-align: center;
}

.login-page h2 {
  font-size: 2rem;
  margin-bottom: 20px;
  color: #2c3e50;
}

.login-page input {
  display: block;
  width: 100%;
  margin-bottom: 15px;
  padding: 12px;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 1rem;
}

.login-page button {
  width: 100%;
  padding: 12px;
  border: none;
  background: #2ecc71;
  color: #fff;
  font-weight: 600;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.2s;
}

.login-page button:hover {
  background: #27ae60;
}

.login-page .error {
  margin-top: 15px;
  color: #e74c3c;
  font-weight: 600;
}
</style>
