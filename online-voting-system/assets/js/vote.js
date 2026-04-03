// ============================================
// VOTE.JS - Voting Page Logic
// ============================================

document.addEventListener('DOMContentLoaded', () => {
  let selectedCandidateId = null;
  let selectedCandidateName = '';

  // --- Candidate Selection ---
  document.querySelectorAll('.candidate-card').forEach(card => {
    card.addEventListener('click', () => {
      if (card.classList.contains('voted-card')) return;

      document.querySelectorAll('.candidate-card').forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');

      selectedCandidateId   = card.dataset.candidateId;
      selectedCandidateName = card.querySelector('.candidate-name').textContent;

      const voteBtn = document.getElementById('vote-btn');
      if (voteBtn) {
        voteBtn.disabled = false;
        voteBtn.classList.add('animate-pulse');
      }
    });
  });

  // --- Vote Button ---
  const voteBtn = document.getElementById('vote-btn');
  if (voteBtn) {
    voteBtn.addEventListener('click', () => {
      if (!selectedCandidateId) {
        Toast.error('Please select a candidate first.');
        return;
      }
      // Populate confirmation modal
      const nameEl = document.getElementById('confirm-candidate-name');
      if (nameEl) nameEl.textContent = selectedCandidateName;
      Modal.open('confirm-modal');
    });
  }

  // --- Confirm Vote ---
  const confirmBtn = document.getElementById('confirm-vote-btn');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', async () => {
      const electionId = document.getElementById('election-id')?.value;
      const csrf       = document.querySelector('[name="csrf_token"]')?.value;

      if (!selectedCandidateId || !electionId) return;

      setLoading(confirmBtn, true);

      try {
        const res = await fetch('../api/vote.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            candidate_id: selectedCandidateId,
            election_id:  electionId,
            csrf_token:   csrf
          })
        });
        const data = await res.json();

        Modal.close('confirm-modal');

        if (data.success) {
          showSuccessAnimation();
        } else {
          Toast.error(data.message || 'Vote failed. Please try again.');
        }
      } catch {
        Toast.error('Network error. Please try again.');
      } finally {
        setLoading(confirmBtn, false);
      }
    });
  }

  // --- Success Animation ---
  function showSuccessAnimation() {
    const overlay = document.getElementById('success-overlay');
    if (overlay) {
      overlay.classList.add('active');
      // Disable all candidate cards
      document.querySelectorAll('.candidate-card').forEach(c => {
        c.classList.add('voted-card');
        c.style.pointerEvents = 'none';
        c.style.opacity = '0.5';
      });
      const selected = document.querySelector(`.candidate-card[data-candidate-id="${selectedCandidateId}"]`);
      if (selected) {
        selected.style.opacity = '1';
        selected.style.borderColor = '#38ef7d';
      }
      if (voteBtn) voteBtn.style.display = 'none';

      // Redirect to results after delay
      setTimeout(() => {
        const electionId = document.getElementById('election-id')?.value;
        window.location.href = `../public/results.php?election_id=${electionId}`;
      }, 3000);
    }
  }

  // --- Real-time vote count update ---
  function loadVoteCounts() {
    const electionId = document.getElementById('election-id')?.value;
    if (!electionId) return;

    fetch(`../api/results.php?election_id=${electionId}`)
      .then(r => r.json())
      .then(data => {
        if (data.results) {
          data.results.forEach(r => {
            const countEl = document.querySelector(`[data-vote-count="${r.id}"]`);
            if (countEl) countEl.textContent = r.vote_count;
          });
        }
      })
      .catch(() => {});
  }

  function setLoading(btn, loading) {
    btn.disabled = loading;
    btn.innerHTML = loading
      ? '<span class="spinner spinner-sm"></span> Submitting...'
      : btn.dataset.text || 'Confirm Vote';
  }
});

// --- Results Charts ---
function initResultsChart(canvasId, labels, data, colors) {
  const canvas = document.getElementById(canvasId);
  if (!canvas || typeof Chart === 'undefined') return;

  new Chart(canvas, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: colors || ['#4facfe', '#667eea', '#ff6a00', '#38ef7d', '#ee0979'],
        borderColor: '#1e293b',
        borderWidth: 3,
        hoverOffset: 8
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { color: '#94a3b8', padding: 20, font: { size: 13 } }
        },
        tooltip: {
          callbacks: {
            label: ctx => ` ${ctx.label}: ${ctx.parsed} votes (${ctx.dataset.data[ctx.dataIndex]})`
          }
        }
      },
      animation: { animateRotate: true, duration: 1200 }
    }
  });
}

function initBarChart(canvasId, labels, data) {
  const canvas = document.getElementById(canvasId);
  if (!canvas || typeof Chart === 'undefined') return;

  new Chart(canvas, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Votes',
        data,
        backgroundColor: labels.map((_, i) => {
          const colors = ['rgba(79,172,254,0.7)', 'rgba(102,126,234,0.7)', 'rgba(255,106,0,0.7)', 'rgba(56,239,125,0.7)'];
          return colors[i % colors.length];
        }),
        borderColor: labels.map((_, i) => {
          const colors = ['#4facfe', '#667eea', '#ff6a00', '#38ef7d'];
          return colors[i % colors.length];
        }),
        borderWidth: 2,
        borderRadius: 8,
        borderSkipped: false
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} votes` } }
      },
      scales: {
        x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
        y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' }, beginAtZero: true }
      },
      animation: { duration: 1200 }
    }
  });
}

// Animate result progress bars
document.addEventListener('DOMContentLoaded', () => {
  setTimeout(() => {
    document.querySelectorAll('.result-progress-fill').forEach(bar => {
      bar.style.width = bar.dataset.width || '0%';
    });
  }, 300);
});
