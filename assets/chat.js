(() => {
	const chat = document.getElementById('chat');
	if (!chat) return;
	const pairId = parseInt(chat.dataset.pairId, 10);
	const log = document.getElementById('chat-log');
	const form = document.getElementById('chat-form');
	const input = document.getElementById('chat-input');
	let lastId = 0;

	async function fetchNew() {
		try {
			const res = await fetch(`/chat_fetch.php?pair_id=${pairId}&after_id=${lastId}`);
			if (!res.ok) return;
			const data = await res.json();
			(data.messages || []).forEach(m => {
				const el = document.createElement('div');
				el.className = 'msg';
				el.innerHTML = `<strong>${escapeHtml(m.username)}:</strong> ${escapeHtml(m.message)} <span class="muted">${m.sent_at}</span>`;
				log.appendChild(el);
				lastId = Math.max(lastId, parseInt(m.id, 10));
				log.scrollTop = log.scrollHeight;
			});
		} catch (e) {}
	}

	form.addEventListener('submit', async (e) => {
		e.preventDefault();
		const text = input.value.trim();
		if (!text) return;
		const body = new URLSearchParams();
		body.set('pair_id', String(pairId));
		body.set('message', text);
		const res = await fetch('/chat_send.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body });
		if (res.ok) {
			input.value = '';
			fetchNew();
		}
	});

	function escapeHtml(s){
		return s.replace(/[&<>\"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;'}[c] || c));
	}

	// initial load and polling
	fetchNew();
	setInterval(fetchNew, 2000);
})();

