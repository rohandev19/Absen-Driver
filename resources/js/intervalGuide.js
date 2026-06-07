/**
 * intervalGuide.js
 * Data and render logic for the Maintenance Component Interval Guide feature.
 * Exported for use in Jest tests via module.exports.
 */

const intervalGuideData = {
  "Isuzu Elf": {
    "Cairan & Pelumas": [
      { name: "Oli Mesin",            km: 10000,  days: 180,  note: null },
      { name: "Air Radiator (flush)", km: 40000,  days: 730,  note: null },
      { name: "Minyak Rem",           km: 40000,  days: 730,  note: null },
      { name: "Oli Power Steering",   km: 40000,  days: 730,  note: null },
      { name: "Oli Transmisi",        km: 40000,  days: 730,  note: null },
    ],
    "Filter": [
      { name: "Filter Oli",           km: 10000,  days: 180,  note: null },
      { name: "Filter Udara",         km: 20000,  days: 365,  note: null },
      { name: "Filter Bahan Bakar",   km: 20000,  days: 365,  note: null },
      { name: "Filter AC / Kabin",    km: 20000,  days: 365,  note: null },
    ],
    "Rem": [
      { name: "Kampas Rem",           km: 40000,  days: 730,  note: null },
      { name: "Cakram Rem",           km: 80000,  days: 1460, note: null },
      { name: "Minyak Rem",           km: 40000,  days: 730,  note: null },
    ],
    "Ban": [
      { name: "Ban Depan Kiri",       km: 80000,  days: 1460, note: "Rotasi setiap 10.000 km" },
      { name: "Ban Depan Kanan",      km: 80000,  days: 1460, note: "Rotasi setiap 10.000 km" },
      { name: "Ban Belakang Kiri",    km: 80000,  days: 1460, note: "Rotasi setiap 10.000 km" },
      { name: "Ban Belakang Kanan",   km: 80000,  days: 1460, note: "Rotasi setiap 10.000 km" },
      { name: "Ban Serep",            km: 80000,  days: 1460, note: null },
    ],
    "Aki & Kelistrikan": [
      { name: "Aki",                  km: null,   days: 730,  note: null },
      { name: "Alternator",           km: null,   days: 1460, note: null },
    ],
    "Lampu": [
      { name: "Lampu Utama",          km: null,   days: 730,  note: null },
      { name: "Lampu Belakang",       km: null,   days: 730,  note: null },
      { name: "Lampu Sein",           km: null,   days: 730,  note: null },
      { name: "Lampu Rem",            km: null,   days: 730,  note: null },
    ],
    "Fan Belt & Selang": [
      { name: "Timing Belt",          km: 100000, days: 1825, note: null },
      { name: "V-Belt / Fan Belt",    km: 40000,  days: 730,  note: null },
      { name: "Selang Radiator",      km: 80000,  days: 1460, note: null },
    ],
    "Kaki-kaki & Suspensi": [
      { name: "Shockbreaker",         km: 80000,  days: 1460, note: null },
      { name: "Ball Joint",           km: 80000,  days: 1460, note: null },
      { name: "Tie Rod",              km: 80000,  days: 1460, note: null },
    ],
    "Mesin": [
      { name: "Busi",                 km: null,   days: null, note: "Tidak berlaku (diesel)" },
      { name: "Koil Pengapian",       km: null,   days: null, note: "Tidak berlaku (diesel)" },
      { name: "Injektor",             km: 80000,  days: 1460, note: null },
    ],
    "Transmisi": [
      { name: "Oli Transmisi",        km: 40000,  days: 730,  note: null },
      { name: "Kampas Kopling",       km: 80000,  days: null, note: null },
    ],
  },

  "Grand Max": {
    "Cairan & Pelumas": [
      { name: "Oli Mesin",            km: 5000,   days: 180,  note: null },
      { name: "Air Radiator (flush)", km: 20000,  days: 730,  note: null },
      { name: "Minyak Rem",           km: 20000,  days: 730,  note: null },
      { name: "Oli Power Steering",   km: 20000,  days: 730,  note: null },
      { name: "Oli Transmisi",        km: 20000,  days: 730,  note: null },
    ],
    "Filter": [
      { name: "Filter Oli",           km: 10000,  days: 180,  note: null },
      { name: "Filter Udara",         km: 10000,  days: 365,  note: null },
      { name: "Filter Bahan Bakar",   km: 20000,  days: 730,  note: null },
      { name: "Filter AC / Kabin",    km: 15000,  days: 365,  note: null },
    ],
    "Rem": [
      { name: "Kampas Rem",           km: 30000,  days: 730,  note: null },
      { name: "Cakram Rem",           km: 60000,  days: 1460, note: null },
      { name: "Minyak Rem",           km: 20000,  days: 730,  note: null },
    ],
    "Ban": [
      { name: "Ban Depan Kiri",       km: 60000,  days: 1095, note: "Rotasi setiap 10.000 km" },
      { name: "Ban Depan Kanan",      km: 60000,  days: 1095, note: "Rotasi setiap 10.000 km" },
      { name: "Ban Belakang Kiri",    km: 60000,  days: 1095, note: "Rotasi setiap 10.000 km" },
      { name: "Ban Belakang Kanan",   km: 60000,  days: 1095, note: "Rotasi setiap 10.000 km" },
      { name: "Ban Serep",            km: 60000,  days: 1095, note: null },
    ],
    "Aki & Kelistrikan": [
      { name: "Aki",                  km: null,   days: 730,  note: null },
      { name: "Alternator",           km: null,   days: 1460, note: null },
    ],
    "Lampu": [
      { name: "Lampu Utama",          km: null,   days: 730,  note: null },
      { name: "Lampu Belakang",       km: null,   days: 730,  note: null },
      { name: "Lampu Sein",           km: null,   days: 730,  note: null },
      { name: "Lampu Rem",            km: null,   days: 730,  note: null },
    ],
    "Fan Belt & Selang": [
      { name: "Timing Belt",          km: 60000,  days: 1460, note: null },
      { name: "V-Belt / Fan Belt",    km: 30000,  days: 730,  note: null },
      { name: "Selang Radiator",      km: 60000,  days: 1460, note: null },
    ],
    "Kaki-kaki & Suspensi": [
      { name: "Shockbreaker",         km: 60000,  days: 1460, note: null },
      { name: "Ball Joint",           km: 60000,  days: 1460, note: null },
      { name: "Tie Rod",              km: 60000,  days: 1460, note: null },
    ],
    "Mesin": [
      { name: "Busi",                 km: 20000,  days: 365,  note: null },
      { name: "Koil Pengapian",       km: 40000,  days: 730,  note: null },
      { name: "Injektor",             km: 60000,  days: 1460, note: null },
    ],
    "Transmisi": [
      { name: "Oli Transmisi",        km: 20000,  days: 730,  note: null },
      { name: "Kampas Kopling",       km: 60000,  days: null, note: null },
    ],
  }
};

function renderIntervalGuide(vehicleType) {
  const data = intervalGuideData[vehicleType];
  const container = document.getElementById('intervalGuideBody');
  if (!data || !container) return;

  const categoryOrder = [
    'Cairan & Pelumas', 'Filter', 'Rem', 'Ban',
    'Aki & Kelistrikan', 'Lampu', 'Fan Belt & Selang',
    'Kaki-kaki & Suspensi', 'Mesin', 'Transmisi'
  ];

  let html = '<div class="accordion accordion-flush" id="guideAccordion">';

  categoryOrder.forEach((cat, idx) => {
    const items = data[cat];
    if (!items || items.length === 0) return; // hide empty categories

    html += `
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button ${idx !== 0 ? 'collapsed' : ''} py-2"
                  type="button" data-bs-toggle="collapse"
                  data-bs-target="#guidecat${idx}">
            ${cat} <span class="badge bg-secondary ms-2">${items.length}</span>
          </button>
        </h2>
        <div id="guidecat${idx}" class="accordion-collapse collapse ${idx === 0 ? 'show' : ''}">
          <div class="accordion-body p-0">
            <table class="table table-sm table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Komponen</th>
                  <th class="text-end">Interval KM</th>
                  <th class="text-end">Interval Hari</th>
                </tr>
              </thead>
              <tbody>`;

    items.forEach(item => {
      const km   = item.km   ? item.km.toLocaleString('id-ID') + ' km'   : (item.note || '-');
      const days = item.days ? item.days + ' hari' : (item.note || '-');
      html += `
        <tr>
          <td>${item.name}</td>
          <td class="text-end"><span class="badge bg-light text-dark border">${km}</span></td>
          <td class="text-end"><span class="badge bg-light text-dark border">${days}</span></td>
        </tr>`;
    });

    html += '</tbody></table></div></div></div>';
  });

  html += '</div>';
  container.innerHTML = html;
}

if (typeof module !== 'undefined' && module.exports) {
  module.exports = { intervalGuideData, renderIntervalGuide };
}
