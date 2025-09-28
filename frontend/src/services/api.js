import axios from "axios";

// -------------------------------------------------------------
// Axios Base API instance
// -------------------------------------------------------------
const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || "http://localhost:8000/api",
});

// -------------------------------------------------------------
// Attach Bearer token automatically
// -------------------------------------------------------------
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("auth_token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// -------------------------------------------------------------
// AUTH FUNCTIONS
// -------------------------------------------------------------

export async function getProfile() {
  try {
    const res = await api.get("/user");
    return res.data;
  } catch (err) {
    console.error("getProfile error:", err.response?.data || err.message);
    return null;
  }
}

export async function login(email, password) {
  try {
    const res = await api.post("/login", { email, password });
    localStorage.setItem("auth_token", res.data.token);
    return res.data.user;
  } catch (err) {
    console.error("login error:", err.response?.data || err.message);
    throw err.response?.data?.message || "Login failed";
  }
}

export async function register(name, email, password) {
  try {
    const res = await api.post("/register", { name, email, password });
    localStorage.setItem("auth_token", res.data.token);
    return res.data.user;
  } catch (err) {
    console.error("register error:", err.response?.data || err.message);
    throw err.response?.data?.message || "Registration failed";
  }
}

export async function logout() {
  try {
    await api.post("/logout");
  } catch (err) {
    console.error("logout error:", err.response?.data || err.message);
  } finally {
    localStorage.removeItem("auth_token");
  }
}

// -------------------------------------------------------------
// TRANSACTION FUNCTIONS
// -------------------------------------------------------------

export async function fetchInitialData() {
  try {
    const res = await api.get("/transactions");
    return res.data;
  } catch (err) {
    console.error("fetchInitialData error:", err.response?.data || err.message);
    throw err.response?.data?.message || "Failed to fetch data";
  }
}

export async function sendTransfer(receiver_id, amount) {
  try {
    const res = await api.post("/transactions", { receiver_id, amount });
    return res.data;
  } catch (err) {
    console.error("sendTransfer error:", err.response?.data || err.message);
    throw err.response?.data?.message || "Transfer failed";
  }
}
