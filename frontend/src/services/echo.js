import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

let echoInstance = null;

export function initEcho(userId, onEvent) {
  if (!userId) return;

  const key = import.meta.env.VITE_PUSHER_KEY || 'your-pusher-key';
  const cluster = import.meta.env.VITE_PUSHER_CLUSTER || 'mt1';

  Pusher.logToConsole = false;

  echoInstance = new Echo({
    broadcaster: 'pusher',
    key,
    cluster,
    wsHost: `ws-${cluster}.pusher.com`,
    wsPort: 443,
    wssPort: 443,
    forceTLS: true,
    encrypted: true
  });

  const channelName = `private-user.${userId}`;

  echoInstance.private(channelName).listen('.TransactionCreated', (e) => {
    onEvent && onEvent(e);
  });

  echoInstance.private(channelName).listen('TransactionCreated', (e) => {
    onEvent && onEvent(e);
  });

  return echoInstance;
}

export { echoInstance };
