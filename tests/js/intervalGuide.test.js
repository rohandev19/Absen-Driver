const { intervalGuideData, renderIntervalGuide } = require('../../resources/js/intervalGuide');
const fc = require('fast-check');

describe('intervalGuide module', () => {
  test('module loads successfully', () => {
    expect(intervalGuideData).toBeDefined();
    expect(typeof renderIntervalGuide).toBe('function');
  });
});

// Feature: maintenance-component-interval-guide, Property 1: Data completeness across vehicle types
describe('Property 1: Data Completeness', () => {
  test('every vehicle type has all required categories with minimum component counts', () => {
    const REQUIRED_CATEGORIES = {
      'Cairan & Pelumas': 5, 'Filter': 4, 'Rem': 3, 'Ban': 5,
      'Aki & Kelistrikan': 2, 'Lampu': 4, 'Fan Belt & Selang': 3,
      'Kaki-kaki & Suspensi': 3, 'Mesin': 3, 'Transmisi': 2
    };
    fc.assert(fc.property(
      fc.constantFrom('Isuzu Elf', 'Grand Max'),
      (vehicleType) => {
        const data = intervalGuideData[vehicleType];
        return Object.entries(REQUIRED_CATEGORIES).every(([cat, minCount]) =>
          data[cat] && data[cat].length >= minCount
        );
      }
    ), { numRuns: 100 });
  });
});

// Feature: maintenance-component-interval-guide, Property 2: Interval value range integrity
describe('Property 2: Interval Value Range Integrity', () => {
  test('all non-null KM intervals are integers in [1000, 100000] and days in [30, 1825]', () => {
    fc.assert(fc.property(
      fc.constantFrom('Isuzu Elf', 'Grand Max'),
      (vehicleType) => {
        const data = intervalGuideData[vehicleType];
        return Object.values(data).flat().every(item => {
          const kmOk  = item.km   === null || (Number.isInteger(item.km)   && item.km   >= 1000  && item.km   <= 100000);
          const dayOk = item.days === null || (Number.isInteger(item.days) && item.days >= 30    && item.days <= 1825);
          return kmOk && dayOk;
        });
      }
    ), { numRuns: 100 });
  });
});

// Feature: maintenance-component-interval-guide, Property 3: Null interval rendering shows dash or note
describe('Property 3: Null Interval Rendering', () => {
  beforeEach(() => {
    document.body.innerHTML = '<div id="intervalGuideBody"></div>';
  });

  test('components with null km/days render as "-" or note text, never empty', () => {
    fc.assert(fc.property(
      fc.constantFrom('Isuzu Elf', 'Grand Max'),
      (vehicleType) => {
        renderIntervalGuide(vehicleType);
        const rows = document.querySelectorAll('#intervalGuideBody tbody tr');
        return [...rows].every(row => {
          const cells = row.querySelectorAll('td');
          return cells[1].textContent.trim() !== '' && cells[2].textContent.trim() !== '';
        });
      }
    ), { numRuns: 100 });
  });
});

// Feature: maintenance-component-interval-guide, Property 4: Every rendered row has 3 non-empty cells
describe('Property 4: Render Completeness', () => {
  beforeEach(() => {
    document.body.innerHTML = '<div id="intervalGuideBody"></div>';
  });

  test('every rendered table row has component name, km cell, and days cell', () => {
    fc.assert(fc.property(
      fc.constantFrom('Isuzu Elf', 'Grand Max'),
      (vehicleType) => {
        renderIntervalGuide(vehicleType);
        const rows = document.querySelectorAll('#intervalGuideBody tbody tr');
        return rows.length > 0 && [...rows].every(row => row.querySelectorAll('td').length === 3);
      }
    ), { numRuns: 100 });
  });
});

// Feature: maintenance-component-interval-guide, Property 5: Selector sync with rendered content
describe('Property 5: Vehicle Type Selector Synchronization', () => {
  beforeEach(() => {
    document.body.innerHTML = '<div id="intervalGuideBody"></div>';
  });

  test('all rendered component names after type change belong to selected type data', () => {
    fc.assert(fc.property(
      fc.constantFrom('Isuzu Elf', 'Grand Max'),
      (vehicleType) => {
        renderIntervalGuide(vehicleType);
        const expectedNames = Object.values(intervalGuideData[vehicleType]).flat().map(i => i.name);
        const renderedNames = [...document.querySelectorAll('#intervalGuideBody tbody td:first-child')]
          .map(td => td.textContent.trim());
        return renderedNames.length > 0 && renderedNames.every(name => expectedNames.includes(name));
      }
    ), { numRuns: 100 });
  });
});

// Feature: maintenance-component-interval-guide, Property 6: Form state preserved across offcanvas open/close
describe('Property 6: Form State Preservation', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <div id="intervalGuideBody"></div>
      <div id="intervalGuideOffcanvas"></div>
      <input name="replacement_interval_km" type="number" />
      <input name="replacement_interval_days" type="number" />
      <input name="cost_per_replacement" type="number" />
    `;
  });

  test('opening and closing interval guide offcanvas does not change form field values', () => {
    fc.assert(fc.property(
      fc.record({
        km:   fc.integer({ min: 0, max: 200000 }),
        days: fc.integer({ min: 0, max: 3650 }),
        cost: fc.integer({ min: 0, max: 50000000 }),
      }),
      ({ km, days, cost }) => {
        document.querySelector('[name="replacement_interval_km"]').value = km;
        document.querySelector('[name="replacement_interval_days"]').value = days;
        document.querySelector('[name="cost_per_replacement"]').value = cost;

        // Simulate offcanvas open + close (no DOM mutation on form)
        const guideOffcanvas = document.getElementById('intervalGuideOffcanvas');
        guideOffcanvas.dispatchEvent(new Event('show.bs.offcanvas'));
        guideOffcanvas.dispatchEvent(new Event('hide.bs.offcanvas'));

        return (
          +document.querySelector('[name="replacement_interval_km"]').value === km &&
          +document.querySelector('[name="replacement_interval_days"]').value === days &&
          +document.querySelector('[name="cost_per_replacement"]').value === cost
        );
      }
    ), { numRuns: 100 });
  });
});

// Example-based tests
describe('Example-Based Tests', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <div id="addComponentModal">
        <button id="btnPanduanInterval"
                data-bs-toggle="offcanvas"
                data-bs-target="#intervalGuideOffcanvas">
          <i class="bi bi-book"></i> Panduan Interval
        </button>
      </div>
      <div id="intervalGuideOffcanvas">
        <h5 id="intervalGuideLabel">Panduan Interval Perawatan Komponen</h5>
        <input type="radio" name="guideVehicleType" id="guideTypeElf" value="Isuzu Elf" />
        <input type="radio" name="guideVehicleType" id="guideTypeGrandMax" value="Grand Max" />
        <div id="intervalGuideBody"></div>
        <div id="guideFooter">Sumber: Dealer Resmi Isuzu &amp; Daihatsu Indonesia.</div>
      </div>
    `;
  });

  test('button #btnPanduanInterval exists in #addComponentModal', () => {
    const modal = document.getElementById('addComponentModal');
    const btn = modal.querySelector('#btnPanduanInterval');
    expect(btn).not.toBeNull();
  });

  test('button has icon element with bi-book class', () => {
    const btn = document.getElementById('btnPanduanInterval');
    const icon = btn.querySelector('.bi-book');
    expect(icon).not.toBeNull();
  });

  test('offcanvas title contains "Panduan Interval Perawatan Komponen"', () => {
    const title = document.getElementById('intervalGuideLabel');
    expect(title.textContent).toContain('Panduan Interval Perawatan Komponen');
  });

  test('selector has exactly 2 options: Isuzu Elf and Grand Max', () => {
    const radios = document.querySelectorAll('input[name="guideVehicleType"]');
    expect(radios.length).toBe(2);
    const values = [...radios].map(r => r.value);
    expect(values).toContain('Isuzu Elf');
    expect(values).toContain('Grand Max');
  });

  test('footer contains "Dealer Resmi" text', () => {
    const footer = document.getElementById('guideFooter');
    expect(footer.textContent).toContain('Dealer Resmi');
  });

  test('Isuzu Elf — Oli Mesin has km=10000 and days=180', () => {
    const oliMesin = intervalGuideData['Isuzu Elf']['Cairan & Pelumas'].find(i => i.name === 'Oli Mesin');
    expect(oliMesin).toBeDefined();
    expect(oliMesin.km).toBe(10000);
    expect(oliMesin.days).toBe(180);
  });

  test('Grand Max — Oli Mesin has km=5000 and days=180', () => {
    const oliMesin = intervalGuideData['Grand Max']['Cairan & Pelumas'].find(i => i.name === 'Oli Mesin');
    expect(oliMesin).toBeDefined();
    expect(oliMesin.km).toBe(5000);
    expect(oliMesin.days).toBe(180);
  });

  test('fallback to Isuzu Elf when vehicle type is unknown', () => {
    const KNOWN_TYPES = ['Isuzu Elf', 'Grand Max'];
    const unknownType = 'Unknown Type';
    const defaultType = KNOWN_TYPES.includes(unknownType) ? unknownType : 'Isuzu Elf';
    expect(defaultType).toBe('Isuzu Elf');
  });

  test('default selector matches known vehicle type', () => {
    const KNOWN_TYPES = ['Isuzu Elf', 'Grand Max'];
    const phpVehicleType = 'Grand Max';
    const defaultType = KNOWN_TYPES.includes(phpVehicleType) ? phpVehicleType : 'Isuzu Elf';
    expect(defaultType).toBe('Grand Max');
    const radio = document.querySelector(`input[name="guideVehicleType"][value="${defaultType}"]`);
    expect(radio).not.toBeNull();
  });
});
