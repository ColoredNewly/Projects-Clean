// ===============================
// ELEMENTS
// ===============================
const searchBtn = document.getElementById("searchBtn");
const destinationInput = document.getElementById("destinationInput");
const destinationInfo = document.getElementById("destinationInfo");
const weatherInfo = document.getElementById("weatherInfo");

const expenseName = document.getElementById("expenseName");
const expenseAmount = document.getElementById("expenseAmount");
const addExpenseBtn = document.getElementById("addExpenseBtn");
const budgetTableBody = document.querySelector("#budgetTable tbody");
const totalAmountEl = document.getElementById("totalAmount");

const todoInput = document.getElementById("todoInput");
const addTodoBtn = document.getElementById("addTodoBtn");
const todoList = document.getElementById("todoList");

const allTripsContainer = document.getElementById("allTripsContainer");

// ===============================
// TRIPS
// ===============================
const tripNameInput = document.getElementById("tripNameInput");
const addTripBtn = document.getElementById("addTripBtn");

// ===============================
// CURRENCY CONVERSION
// ===============================
const homeCurrency = document.getElementById("homeCurrency");
const convertBtn = document.getElementById("convertBtn");
const convertedResult = document.getElementById("convertedResult");

let destinationCurrencyCode = "EUR"; // default

let trips = JSON.parse(localStorage.getItem("trips")) || [];
let currentTripId = localStorage.getItem("currentTripId") || null;

// ===============================
// STATE
// ===============================
let expenses = JSON.parse(localStorage.getItem("expenses")) || [];
let todos = JSON.parse(localStorage.getItem("todos")) || [];

// ===============================
// DESTINATION SEARCH
// ===============================
searchBtn.addEventListener("click", () => {
  const query = destinationInput.value.trim();
  if (!query) return;

  getCountryInfo(query);
});

// ===============================
// REST COUNTRIES
// ===============================
async function getCountryInfo(countryName) {
  try {
    destinationInfo.innerHTML = "Loading...";
    weatherInfo.innerHTML = "Loading...";

    const res = await fetch(
      `https://restcountries.com/v3.1/name/${encodeURIComponent(countryName)}`
    );
    const data = await res.json();
    if (!data || !data[0]) throw new Error("No country found");

    const country = data[0];

    // Extract info
    const population = country.population.toLocaleString();
    const language = Object.values(country.languages || {})[0];
    const currencyObj = Object.entries(country.currencies || {})[0];
    const currencyCode = currencyObj ? currencyObj[0] : "EUR";
    const currencyName = currencyObj ? currencyObj[1].name : "Unknown";
    destinationCurrencyCode = currencyCode;
    renderExpenses();

    const flag = country.flags.png;
    const lat = country.latlng[0];
    const lon = country.latlng[1];

    // Display country info
    destinationInfo.innerHTML = `
      <img src="${flag}" width="80" />
      <p><strong>Држава:</strong> ${country.name.common}</p>
      <p><strong>Население:</strong> ${population}</p>
      <p><strong>Јазик:</strong> ${language}</p>
      <p><strong>Валута:</strong> ${currencyName} (${currencyCode})</p>
    `;

    // Save lat/lon temporarily for weather
    destinationInfo.dataset.lat = lat;
    destinationInfo.dataset.lon = lon;

    // Fetch weather only if there's a current trip
    if (currentTripId) {
      getWeather(lat, lon, currentTripId);
    }
  } catch (err) {
    console.log(err);
    destinationInfo.innerHTML = "❌ Не се пронајдени податоци.";
    weatherInfo.innerHTML = "—";
  }
}

// ===============================
// WEATHER (Open-Meteo)
// ===============================
async function getWeather(lat, lon, tripId) {
  try {
    const res = await fetch(
      `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true`
    );
    const data = await res.json();

    weatherInfo.innerHTML = `
      🌡 Температура: ${data.current_weather.temperature}°C<br>
      💨 Ветер: ${data.current_weather.windspeed} km/h
    `;

    const trip = trips.find((t) => t.id === tripId);
    if (trip) {
      trip.weather = {
        temperature: data.current_weather.temperature,
        windspeed: data.current_weather.windspeed,
        winddirection: data.current_weather.winddirection,
      };
      saveTrips(); // re-render trips
    }
  } catch (err) {
    weatherInfo.textContent = "❌ Грешка при прибирање на времето";
    console.log(err);
  }
}

// ===============================
// ADD TRIP
// ===============================
addTripBtn.addEventListener("click", () => {
  const name = tripNameInput.value.trim();
  if (!name) return;

  let convertedTotal = null;
  let convertedCurrency = null;
  if (convertedResult.textContent.includes("=")) {
    const parts = convertedResult.textContent.split("=");
    convertedTotal = Number(parts[1].trim().split(" ")[0]);
    convertedCurrency = parts[1].trim().split(" ")[1];
  }

  const trip = {
    id: Date.now().toString(),
    name,
    expenses: expenses,
    todos: todos,
    weather: null, // initially null
    convertedTotal: convertedTotal,
    convertedCurrency: convertedCurrency,
  };

  trips.push(trip);
  currentTripId = trip.id;
  saveTrips();

  // Fetch weather if user has searched a country before adding trip
  const lat = destinationInfo.dataset.lat;
  const lon = destinationInfo.dataset.lon;
  if (lat && lon) getWeather(lat, lon, trip.id);

  // Clear inputs and state
  tripNameInput.value = "";
  expenseName.value = "";
  expenseAmount.value = "";
  todoInput.value = "";
  convertedResult.textContent = "—";
  homeCurrency.value = "";

  expenses = [];
  todos = [];
  renderExpenses();
  renderTodos();
});

// ===============================
// CONVERT CURRENCY
// ===============================
convertBtn.addEventListener("click", async () => {
  try {
    const totalText = totalAmountEl.textContent.split(" ")[0];
    const total = Number(totalText);

    if (!total) {
      convertedResult.textContent = "Нема износ за конверзија.";
      return;
    }

    convertedResult.textContent = "Converting...";

    const targetCurrency = homeCurrency.value;

    const res = await fetch(
      `https://open.er-api.com/v6/latest/${destinationCurrencyCode}`
    );
    const data = await res.json();
    const rate = data.rates[targetCurrency];

    if (!rate) {
      convertedResult.textContent = "❌ Нема курс.";
      return;
    }

    const converted = total * rate;
    convertedResult.textContent = `${total.toFixed(
      2
    )} ${destinationCurrencyCode} = ${converted.toFixed(2)} ${targetCurrency}`;

    const trip = trips.find((t) => t.id === currentTripId);
    if (trip) {
      trip.convertedTotal = converted;
      trip.convertedCurrency = targetCurrency;
      saveTrips();
    }
  } catch (err) {
    convertedResult.textContent = "❌ Грешка при конверзија.";
  }
});

// ===============================
// RENDER TRIPS
// ===============================
function renderAllTrips() {
  allTripsContainer.innerHTML = "";

  if (trips.length === 0) {
    allTripsContainer.innerHTML = "<p>Нема зачувани патувања.</p>";
    return;
  }

  trips.forEach((trip) => {
    const total =
      trip.convertedTotal ||
      trip.expenses.reduce((sum, e) => sum + e.amount, 0);
    const currency = trip.convertedCurrency || destinationCurrencyCode;

    const div = document.createElement("div");
    div.className = "card";
    div.style.marginBottom = "15px";

    div.innerHTML = `
      <h3>🧳 ${trip.name}</h3>
      <p><strong>Вкупен буџет:</strong> ${total.toFixed(2)} ${currency}</p>

      <p><strong>Трошоци:</strong></p>
      <ul>
        ${
          trip.expenses.length === 0
            ? "<li>Нема трошоци</li>"
            : trip.expenses
                .map((e) => `<li>${e.name} — ${e.amount.toFixed(2)} ${currency}</li>`)
                .join("")
        }
      </ul>

      <p><strong>Активности:</strong></p>
      <ul>
        ${
          trip.todos.length === 0
            ? "<li>Нема активности</li>"
            : trip.todos
                .map(
                  (t) =>
                    `<li style="${
                      t.done ? "text-decoration: line-through; color: gray;" : ""
                    }">${t.text}</li>`
                )
                .join("")
        }
      </ul>

      ${
        trip.weather
          ? `<p><strong>Време:</strong> ${trip.weather.temperature}°C, ветер ${trip.weather.windspeed} km/h</p>`
          : ""
      }

      <button class="deleteTripBtn" data-id="${trip.id}">🗑 Избриши патување</button>
    `;

    allTripsContainer.appendChild(div);
  });

  document.querySelectorAll(".deleteTripBtn").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.id;
      trips = trips.filter((t) => t.id !== id);
      if (currentTripId === id) currentTripId = trips[0]?.id || null;
      saveTrips();
      loadCurrentTrip();
    });
  });
}

// ===============================
// CURRENT TRIP STATE
// ===============================
function loadCurrentTrip() {
  const trip = trips.find((t) => t.id === currentTripId);

  if (!trip) {
    expenses = [];
    todos = [];
  } else {
    expenses = trip.expenses;
    todos = trip.todos;
  }

  renderExpenses();
  renderTodos();
}

function saveTrips() {
  localStorage.setItem("trips", JSON.stringify(trips));
  localStorage.setItem("currentTripId", currentTripId);
  renderAllTrips();
}

// ===============================
// BUDGET PLANNER
// ===============================
addExpenseBtn.addEventListener("click", () => {
  const name = expenseName.value.trim();
  const amount = Number(expenseAmount.value);
  if (!name || !amount) return;

  const expense = { id: Date.now(), name, amount };
  expenses.push(expense);
  saveExpenses();
  renderExpenses();

  expenseName.value = "";
  expenseAmount.value = "";
});

function renderExpenses() {
  budgetTableBody.innerHTML = "";

  const trip = trips.find((t) => t.id === currentTripId);
  const currency = trip?.convertedCurrency || destinationCurrencyCode;

  let total = 0;

  expenses.forEach((exp) => {
    total += exp.amount;
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${exp.name}</td>
      <td>${exp.amount.toFixed(2)} ${currency}</td>
      <td><button onclick="deleteExpense(${exp.id})">Избриши</button></td>
    `;
    budgetTableBody.appendChild(tr);
  });

  totalAmountEl.textContent = `${total.toFixed(2)} ${currency}`;
}

function deleteExpense(id) {
  expenses = expenses.filter((e) => e.id !== id);
  saveExpenses();
  renderExpenses();
}

function saveExpenses() {
  const trip = trips.find((t) => t.id === currentTripId);
  if (!trip) return;
  trip.expenses = expenses;
  saveTrips();
}

// ===============================
// TODO LIST
// ===============================
addTodoBtn.addEventListener("click", () => {
  const text = todoInput.value.trim();
  if (!text) return;

  const todo = { id: Date.now(), text, done: false };
  todos.push(todo);
  saveTodos();
  renderTodos();
  todoInput.value = "";
});

function renderTodos() {
  todoList.innerHTML = "";
  todos.forEach((todo) => {
    const li = document.createElement("li");
    if (todo.done) li.classList.add("todo-done");

    li.innerHTML = `
      <span onclick="toggleTodo(${todo.id})">${todo.text}</span>
      <button onclick="deleteTodo(${todo.id})">X</button>
    `;
    todoList.appendChild(li);
  });
}

function toggleTodo(id) {
  todos = todos.map((t) => (t.id === id ? { ...t, done: !t.done } : t));
  saveTodos();
  renderTodos();
}

function deleteTodo(id) {
  todos = todos.filter((t) => t.id !== id);
  saveTodos();
  renderTodos();
}

function saveTodos() {
  const trip = trips.find((t) => t.id === currentTripId);
  if (!trip) return;
  trip.todos = todos;
  saveTrips();
}

// ===============================
// LOAD CURRENCIES
// ===============================
async function loadAllCurrencies() {
  try {
    const res = await fetch(`https://open.er-api.com/v6/latest/EUR`);
    const data = await res.json();
    const codes = Object.keys(data.rates);

    const datalist = document.getElementById("currenciesList");
    datalist.innerHTML = "";
    codes.forEach((code) => {
      const option = document.createElement("option");
      option.value = code;
      datalist.appendChild(option);
    });
  } catch (err) {
    console.log("Error loading currencies:", err);
  }
}

loadAllCurrencies();

// ===============================
// INIT
// ===============================
loadCurrentTrip();
renderAllTrips();
