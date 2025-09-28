Mini Wallet Application – Quick Start
1. Backend (Laravel API + Pusher)

Steps:

Navigate to backend folder:

cd backend


Install dependencies and setup environment (run once):

composer install

cp .env.example .env

php artisan key:generate

php artisan migrate --seed


Configure Pusher in .env:

PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_key
PUSHER_APP_SECRET=your_pusher_secret
PUSHER_APP_CLUSTER=your_pusher_cluster


Start backend server:

php artisan serve


API URL: http://127.0.0.1:8000/api

Optional: Run queue worker (required if using queued events for Pusher):

php artisan queue:work

2. Frontend (Vue.js + Pusher)

Steps:

Navigate to frontend folder:

cd frontend


Install dependencies and setup environment (run once):

npm install
cp .env.example .env


Configure Pusher in .env:

VITE_PUSHER_APP_KEY=your_pusher_key
VITE_PUSHER_CLUSTER=your_pusher_cluster
VITE_API_URL=http://127.0.0.1:8000/api


Start frontend server:

npm run dev


Frontend URL: http://localhost:5173

Frontend will automatically listen to Pusher events and update balances & transactions in real-time.
