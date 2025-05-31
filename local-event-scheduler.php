<?php
// index.php
// Questo file PHP serve solo a consegnare l'HTML; tutta la logica è implementata lato client.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <!-- Assicura la corretta scala della viewport -->
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Local Event Aggregator</title>
  
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <!-- FullCalendar CSS -->
  <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
  
  <style>
    /* Base Styling */
    body {
      background-color: #f8f9fa;
      padding-top: 20px;
      padding-bottom: 20px;
    }
    .container {
      max-width: 960px;
    }
    h1 {
      text-align: center;
      margin-bottom: 30px;
    }
    /* Navigation Tabs */
    .nav-tabs .nav-link {
      cursor: pointer;
    }
    /* Sezioni principali */
    .section {
      display: none;
      margin-top: 20px;
    }
    .section.active {
      display: block;
    }
    /* Manage Events - Form styling */
    .event-form .form-group {
      margin-bottom: 15px;
    }
    /* Table styling for Manage Events */
    table {
      width: 100%;
    }
    /* Export/Import buttons */
    .export-import-btns button {
      margin-right: 10px;
      margin-top: 10px;
    }
    /* Filtering controls */
    .filter-controls {
      margin-top: 15px;
      margin-bottom: 20px;
    }
  </style>
  
</head>
<body>
  <div class="container">
    <h1>Local Event Aggregator</h1>
    
    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs" id="mainTabs">
      <li class="nav-item">
        <a class="nav-link active" data-target="calendarView">Calendar View</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-target="listView">List View</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-target="manageEvents">Manage Events</a>
      </li>
    </ul>
    
    <!-- Refresh Button -->
    <button id="refreshBtn" class="btn btn-primary mt-3">Refresh Events</button>
    
    <!-- Calendar View Section -->
    <div id="calendarView" class="section active">
      <div id="calendar"></div>
    </div>
    
    <!-- List View Section -->
    <div id="listView" class="section">
      <div id="listContainer" class="mt-3"></div>
    </div>
    
    <!-- Manage Events Section -->
    <div id="manageEvents" class="section">
      <div class="card mb-4">
        <div class="card-header">Add / Edit Event</div>
        <div class="card-body">
          <form id="eventForm" class="event-form">
            <div class="form-group mb-2">
              <label for="eventTitle">Title</label>
              <input type="text" id="eventTitle" class="form-control" placeholder="Enter event title" required>
            </div>
            <div class="form-group mb-2">
              <label for="eventDescription">Description</label>
              <textarea id="eventDescription" class="form-control" rows="2" placeholder="Enter event description" required></textarea>
            </div>
            <div class="form-group mb-2">
              <label for="eventStart">Start Date & Time</label>
              <input type="datetime-local" id="eventStart" class="form-control" required>
            </div>
            <div class="form-group mb-2">
              <label for="eventEnd">End Date & Time</label>
              <input type="datetime-local" id="eventEnd" class="form-control" required>
            </div>
            <div class="form-group mb-2">
              <label for="eventLocation">Location</label>
              <input type="text" id="eventLocation" class="form-control" placeholder="Enter event location">
            </div>
            <button type="submit" id="saveEventBtn" class="btn btn-primary">Save Event</button>
            <button type="button" id="clearEventFormBtn" class="btn btn-secondary">Clear Form</button>
          </form>
        </div>
      </div>
      
      <!-- Filtering Controls for Exporting Events -->
      <div class="card mb-4">
        <div class="card-header">Export Events by Period</div>
        <div class="card-body">
          <div class="filter-controls">
            <label for="filterType" class="form-label">Period:</label>
            <select id="filterType" class="form-select" style="width: auto; display: inline-block;">
              <option value="day">Day</option>
              <option value="week">Week</option>
              <option value="month">Month</option>
              <option value="year">Year</option>
            </select>
            <input type="date" id="filterDate" class="form-control d-inline-block" style="width: auto; margin-left: 10px;">
            <button id="exportFilteredBtn" class="btn btn-info">Export Filtered Events (CSV)</button>
          </div>
          <div class="export-import-btns">
            <button id="exportEventsJSONBtn" class="btn btn-success">Export All Events (JSON)</button>
            <button id="exportEventsCSVBtn" class="btn btn-info">Export All Events (CSV)</button>
            <input type="file" id="importEventsInput" style="display:none;" accept="application/json">
            <button id="importEventsBtn" class="btn btn-outline-success">Import Events (JSON)</button>
            <button id="printEventsBtn" class="btn btn-secondary">Print Events</button>
          </div>
        </div>
      </div>
      
      <!-- Manage Events Table -->
      <div class="card">
        <div class="card-header">Manage Events</div>
        <div class="card-body">
          <table class="table" id="eventTable">
            <thead>
              <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Start</th>
                <th>End</th>
                <th>Location</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- Event rows loaded dinamicamente -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
  </div> <!-- End Container -->
  
  <!-- Modal per l'Editing degli Eventi -->
  <div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editEventModalLabel">Edit Event</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editEventForm">
            <input type="hidden" id="editEventId">
            <div class="mb-2">
              <label for="editEventTitle" class="form-label">Title</label>
              <input type="text" id="editEventTitle" class="form-control" required>
            </div>
            <div class="mb-2">
              <label for="editEventDescription" class="form-label">Description</label>
              <textarea id="editEventDescription" class="form-control" rows="2" required></textarea>
            </div>
            <div class="mb-2">
              <label for="editEventStart" class="form-label">Start Date & Time</label>
              <input type="datetime-local" id="editEventStart" class="form-control" required>
            </div>
            <div class="mb-2">
              <label for="editEventEnd" class="form-label">End Date & Time</label>
              <input type="datetime-local" id="editEventEnd" class="form-control" required>
            </div>
            <div class="mb-2">
              <label for="editEventLocation" class="form-label">Location</label>
              <input type="text" id="editEventLocation" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Bootstrap Bundle with Popper JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- FullCalendar JS -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
  
  <script>
    /***********************************************************************
     * Local Event Aggregator
     * 
     * English: A webapp that aggregates local events from open data sources.
     *          You can add, edit, and delete events.
     *          The events are shown in a Calendar (using FullCalendar) and in a List View.
     *          Additionally, the events can be exported/imported (JSON and CSV)
     *          and filtered by day, week, month, or year for office use.
     *
     * Italiano: Una webapp che aggrega eventi locali da fonti open data.
     *           È possibile aggiungere, modificare ed eliminare eventi.
     *           Gli eventi sono visualizzati in un Calendario (FullCalendar) e in una Vista Elenco.
     *           Inoltre, gli eventi possono essere esportati/importati (JSON e CSV)
     *           e filtrati per giorno, settimana, mese o anno per usi ufficio.
     ***********************************************************************/
    
    // STORAGE: Utilizziamo localStorage per salvare gli eventi.
    let eventsData = JSON.parse(localStorage.getItem("events")) || [];
    // Esempio di eventi se vuoto (opzionale, si può commentare)
    if (eventsData.length === 0) {
      eventsData = [
        {
          id: "1",
          title: "Community Concert",
          description: "Live music in the park.",
          start: "2025-06-10T18:00",
          end: "2025-06-10T21:00",
          location: "Central Park"
        },
        {
          id: "2",
          title: "Art Exhibition",
          description: "Local artists showcase their work.",
          start: "2025-06-15T10:00",
          end: "2025-06-15T17:00",
          location: "City Gallery"
        }
      ];
      saveEvents();
    }
    
    function saveEvents() {
      localStorage.setItem("events", JSON.stringify(eventsData));
    }
    
    function loadEvents() {
      eventsData = JSON.parse(localStorage.getItem("events")) || [];
    }
    
    // ---------------------------
    // FULLCALENDAR INITIALIZATION
    // ---------------------------
    let calendar;
    async function initializeCalendar() {
      loadEvents();
      if (!calendar) {
        calendar = new FullCalendar.Calendar(document.getElementById("calendar"), {
          initialView: "dayGridMonth",
          headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,timeGridWeek,timeGridDay"
          },
          events: eventsData,
          eventClick: function(info) {
            // On click, open modal per edit
            openEditEventModal(info.event.extendedProps.id);
          }
        });
        calendar.render();
      } else {
        calendar.removeAllEventSources();
        calendar.addEventSource(eventsData);
      }
    }
    
    // ---------------------------
    // LIST VIEW RENDERING
    // ---------------------------
    async function renderListView() {
      loadEvents();
      const container = document.getElementById("listContainer");
      container.innerHTML = "";
      if (eventsData.length === 0) {
        container.innerHTML = "<p>No events found.</p>";
        return;
      }
      eventsData.forEach(ev => {
        const card = document.createElement("div");
        card.className = "card mb-3";
        card.innerHTML = `
          <div class="card-body">
            <h5 class="card-title">${ev.title}</h5>
            <p class="card-text">${ev.description}</p>
            <p class="card-text"><small class="text-muted">
              ${new Date(ev.start).toLocaleString()} - ${new Date(ev.end).toLocaleString()}<br>
              Location: ${ev.location}
            </small></p>
            <button class="btn btn-sm btn-warning edit-event-btn" data-id="${ev.id}">Edit</button>
            <button class="btn btn-sm btn-danger delete-event-btn" data-id="${ev.id}">Delete</button>
          </div>
        `;
        container.appendChild(card);
      });
      // Bind edit and delete buttons
      document.querySelectorAll(".edit-event-btn").forEach(btn => {
        btn.addEventListener("click", (e) => {
          const id = e.target.getAttribute("data-id");
          openEditEventModal(id);
        });
      });
      document.querySelectorAll(".delete-event-btn").forEach(btn => {
        btn.addEventListener("click", (e) => {
          const id = e.target.getAttribute("data-id");
          if (confirm("Delete this event?")) {
            deleteEvent(id);
          }
        });
      });
    }
    
    // ---------------------------
    // MANAGE EVENTS SECTION
    // ---------------------------
    function renderEventsTable() {
      loadEvents();
      const tbody = document.querySelector("#eventTable tbody");
      tbody.innerHTML = "";
      if (eventsData.length === 0) {
        tbody.innerHTML = "<tr><td colspan='6' class='text-center'>No events found.</td></tr>";
        return;
      }
      eventsData.forEach(ev => {
        const tr = document.createElement("tr");
        const tdTitle = document.createElement("td");
        tdTitle.textContent = ev.title;
        const tdDesc = document.createElement("td");
        tdDesc.textContent = ev.description;
        const tdStart = document.createElement("td");
        tdStart.textContent = new Date(ev.start).toLocaleString();
        const tdEnd = document.createElement("td");
        tdEnd.textContent = new Date(ev.end).toLocaleString();
        const tdLocation = document.createElement("td");
        tdLocation.textContent = ev.location;
        const tdActions = document.createElement("td");
        const editBtn = document.createElement("button");
        editBtn.className = "btn btn-sm btn-warning";
        editBtn.textContent = "Edit";
        editBtn.addEventListener("click", () => {
          openEditEventModal(ev.id);
        });
        const deleteBtn = document.createElement("button");
        deleteBtn.className = "btn btn-sm btn-danger";
        deleteBtn.textContent = "Delete";
        deleteBtn.addEventListener("click", () => {
          if (confirm("Delete this event?")) {
            deleteEvent(ev.id);
          }
        });
        tdActions.append(editBtn, " ", deleteBtn);
        tr.append(tdTitle, tdDesc, tdStart, tdEnd, tdLocation, tdActions);
        tbody.appendChild(tr);
      });
    }
    
    // ---------------------------
    // EVENT CRUD FUNCTIONS
    // ---------------------------
    // Add new event from Manage Events form
    document.getElementById("eventForm").addEventListener("submit", (e) => {
      e.preventDefault();
      const title = document.getElementById("eventTitle").value.trim();
      const description = document.getElementById("eventDescription").value.trim();
      const start = document.getElementById("eventStart").value;
      const end = document.getElementById("eventEnd").value;
      const location = document.getElementById("eventLocation").value.trim();
      if (!title || !start || !end) {
        alert("Please fill in required fields.");
        return;
      }
      const newEvent = {
        id: Date.now().toString(),
        title,
        description,
        start,
        end,
        location
      };
      eventsData.push(newEvent);
      saveEvents();
      alert("Event added successfully!");
      document.getElementById("eventForm").reset();
      initializeCalendar();
      renderListView();
      renderEventsTable();
    });
    
    // Delete event by id
    function deleteEvent(id) {
      eventsData = eventsData.filter(ev => ev.id !== id);
      saveEvents();
      alert("Event deleted.");
      initializeCalendar();
      renderListView();
      renderEventsTable();
    }
    
    // ---------------------------
    // EDIT EVENT VIA MODAL
    // ---------------------------
    function openEditEventModal(id) {
      loadEvents();
      const eventObj = eventsData.find(ev => ev.id === id);
      if (!eventObj) return;
      document.getElementById("editEventId").value = eventObj.id;
      document.getElementById("editEventTitle").value = eventObj.title;
      document.getElementById("editEventDescription").value = eventObj.description;
      document.getElementById("editEventStart").value = eventObj.start;
      document.getElementById("editEventEnd").value = eventObj.end;
      document.getElementById("editEventLocation").value = eventObj.location;
      const modalEl = document.getElementById("editEventModal");
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    }
    
    document.getElementById("editEventForm").addEventListener("submit", (e) => {
      e.preventDefault();
      const id = document.getElementById("editEventId").value;
      const title = document.getElementById("editEventTitle").value.trim();
      const description = document.getElementById("editEventDescription").value.trim();
      const start = document.getElementById("editEventStart").value;
      const end = document.getElementById("editEventEnd").value;
      const location = document.getElementById("editEventLocation").value.trim();
      if (!title || !start || !end) {
        alert("Please fill in required fields.");
        return;
      }
      const idx = eventsData.findIndex(ev => ev.id === id);
      if (idx === -1) return;
      eventsData[idx] = { id, title, description, start, end, location };
      saveEvents();
      alert("Event updated successfully!");
      initializeCalendar();
      renderListView();
      renderEventsTable();
      const modalEl = document.getElementById("editEventModal");
      const modal = bootstrap.Modal.getInstance(modalEl);
      modal.hide();
    });
    
    // Clear Manage Events form
    document.getElementById("clearEventFormBtn").addEventListener("click", () => {
      document.getElementById("eventForm").reset();
    });
    
    // ---------------------------
    // IMPORT / EXPORT EVENTS (JSON & CSV)
    // ---------------------------
    // Export all events as JSON
    document.getElementById("exportEventsJSONBtn")?.addEventListener("click", () => {
      loadEvents();
      const jsonContent = JSON.stringify(eventsData, null, 2);
      const blob = new Blob([jsonContent], { type: "application/json;charset=utf-8" });
      const url = URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = "events.json";
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    });
    // Export all events as CSV
    document.getElementById("exportEventsCSVBtn")?.addEventListener("click", () => {
      loadEvents();
      if (eventsData.length === 0) {
        alert("No events to export.");
        return;
      }
      const csvContent = generateCSV(eventsData);
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.setAttribute("href", url);
      link.setAttribute("download", "events.csv");
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    });
    // Import events from JSON
    document.getElementById("importEventsBtn").addEventListener("click", () => {
      document.getElementById("importEventsInput").click();
    });
    document.getElementById("importEventsInput").addEventListener("change", (e) => {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          try {
            eventsData = JSON.parse(e.target.result);
            saveEvents();
            alert("Events imported successfully.");
            initializeCalendar();
            renderListView();
            renderEventsTable();
          } catch (err) {
            alert("Error reading JSON file.");
          }
        };
        reader.readAsText(file);
      }
    });
    
    // Generate CSV from eventsData
    function generateCSV(data) {
      const headers = ["id", "title", "description", "start", "end", "location"];
      let csv = headers.join(",") + "\n";
      data.forEach(item => {
        const row = headers.map(header =>
          `"${String(item[header]).replace(/"/g, '""')}"`).join(",");
        csv += row + "\n";
      });
      return csv;
    }
    
    // ---------------------------
    // EXPORT FILTERED EVENTS (by period)
    // ---------------------------
    document.getElementById("exportFilteredBtn").addEventListener("click", () => {
      const filterType = document.getElementById("filterType").value; // day, week, month, year
      const filterDate = document.getElementById("filterDate").value;
      if (!filterDate) {
        alert("Please select a base date for filtering.");
        return;
      }
      const baseDate = new Date(filterDate);
      let startBoundary, endBoundary;
      if (filterType === "day") {
        startBoundary = new Date(baseDate.getFullYear(), baseDate.getMonth(), baseDate.getDate());
        endBoundary = new Date(baseDate.getFullYear(), baseDate.getMonth(), baseDate.getDate(), 23, 59, 59);
      } else if (filterType === "week") {
        // Assuming week starts on Monday
        const day = baseDate.getDay();
        // In JavaScript, Sunday=0, so adjust: if day==0, set to 7
        const adjustedDay = day === 0 ? 7 : day;
        startBoundary = new Date(baseDate);
        startBoundary.setDate(baseDate.getDate() - adjustedDay + 1);
        startBoundary.setHours(0,0,0);
        endBoundary = new Date(startBoundary);
        endBoundary.setDate(endBoundary.getDate() + 6);
        endBoundary.setHours(23,59,59);
      } else if (filterType === "month") {
        startBoundary = new Date(baseDate.getFullYear(), baseDate.getMonth(), 1);
        endBoundary = new Date(baseDate.getFullYear(), baseDate.getMonth()+1, 0, 23,59,59);
      } else if (filterType === "year") {
        startBoundary = new Date(baseDate.getFullYear(), 0, 1);
        endBoundary = new Date(baseDate.getFullYear(), 11, 31, 23,59,59);
      }
      loadEvents();
      const filteredEvents = eventsData.filter(ev => {
        const eventStart = new Date(ev.start);
        return eventStart >= startBoundary && eventStart <= endBoundary;
      });
      if (filteredEvents.length === 0) {
        alert("No events found in selected period.");
        return;
      }
      const csvContent = generateCSV(filteredEvents);
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.setAttribute("href", url);
      link.setAttribute("download", `events_${filterType}_${filterDate}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    });
    
    // ---------------------------
    // PRINT EVENTS (for Office use)
    // ---------------------------
    document.getElementById("printEventsBtn").addEventListener("click", () => {
      window.print();
    });
    
    // ---------------------------
    // FULLCALENDAR & LIST VIEW RENDERING
    // ---------------------------
    async function initializeCalendar() {
      loadEvents();
      if (!calendar) {
        calendar = new FullCalendar.Calendar(document.getElementById("calendar"), {
          initialView: "dayGridMonth",
          headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,timeGridWeek,timeGridDay"
          },
          events: eventsData,
          eventClick: function(info) {
            // Apri la modale di edit dell'evento (usiamo l'id memorizzato in extendedProps)
            openEditEventModal(info.event.extendedProps.id);
          },
          eventDidMount: function(info) {
            // Assicurati che l'evento abbia la proprietà id in extendedProps
            if (!info.event.extendedProps.id) {
              info.event.setExtendedProp("id", info.event.id);
            }
          }
        });
        calendar.render();
      } else {
        calendar.removeAllEventSources();
        calendar.addEventSource(eventsData);
      }
    }
    
    async function renderListView() {
      loadEvents();
      const container = document.getElementById("listContainer");
      container.innerHTML = "";
      if (eventsData.length === 0) {
        container.innerHTML = "<p>No events found.</p>";
        return;
      }
      eventsData.forEach(ev => {
        const card = document.createElement("div");
        card.className = "card mb-3";
        card.innerHTML = `
          <div class="card-body">
            <h5 class="card-title">${ev.title}</h5>
            <p class="card-text">${ev.description}</p>
            <p class="card-text"><small class="text-muted">
              ${new Date(ev.start).toLocaleString()} - ${new Date(ev.end).toLocaleString()}<br>
              Location: ${ev.location}
            </small></p>
            <button class="btn btn-sm btn-warning edit-event-btn" data-id="${ev.id}">Edit</button>
            <button class="btn btn-sm btn-danger delete-event-btn" data-id="${ev.id}">Delete</button>
          </div>
        `;
        container.appendChild(card);
      });
      document.querySelectorAll(".edit-event-btn").forEach(btn => {
        btn.addEventListener("click", (e) => {
          const id = e.target.getAttribute("data-id");
          openEditEventModal(id);
        });
      });
      document.querySelectorAll(".delete-event-btn").forEach(btn => {
        btn.addEventListener("click", (e) => {
          const id = e.target.getAttribute("data-id");
          if (confirm("Delete this event?")) {
            deleteEvent(id);
          }
        });
      });
    }
    
    function renderEventsTable() {
      loadEvents();
      const tbody = document.querySelector("#eventTable tbody");
      tbody.innerHTML = "";
      if (eventsData.length === 0) {
        tbody.innerHTML = "<tr><td colspan='6' class='text-center'>No events found.</td></tr>";
        return;
      }
      eventsData.forEach(ev => {
        const tr = document.createElement("tr");
        const tdTitle = document.createElement("td");
        tdTitle.textContent = ev.title;
        const tdDesc = document.createElement("td");
        tdDesc.textContent = ev.description;
        const tdStart = document.createElement("td");
        tdStart.textContent = new Date(ev.start).toLocaleString();
        const tdEnd = document.createElement("td");
        tdEnd.textContent = new Date(ev.end).toLocaleString();
        const tdLoc = document.createElement("td");
        tdLoc.textContent = ev.location;
        const tdActions = document.createElement("td");
        const editBtn = document.createElement("button");
        editBtn.className = "btn btn-sm btn-warning";
        editBtn.style.marginRight = "5px";
        editBtn.textContent = "Edit";
        editBtn.addEventListener("click", () => {
          openEditEventModal(ev.id);
        });
        const delBtn = document.createElement("button");
        delBtn.className = "btn btn-sm btn-danger";
        delBtn.textContent = "Delete";
        delBtn.addEventListener("click", () => {
          if (confirm("Delete this event?")) {
            deleteEvent(ev.id);
          }
        });
        tdActions.appendChild(editBtn);
        tdActions.appendChild(delBtn);
        tr.append(tdTitle, tdDesc, tdStart, tdEnd, tdLoc, tdActions);
        tbody.appendChild(tr);
      });
    }
    
    async function updateEventViews() {
      await initializeCalendar();
      await renderListView();
      renderEventsTable();
    }
    
    // ---------------------------
    // TAB NAVIGATION
    // ---------------------------
    const mainTabs = document.getElementById("mainTabs");
    mainTabs.addEventListener("click", (e) => {
      const targetTab = e.target.closest(".nav-link");
      if (!targetTab) return;
      Array.from(mainTabs.children).forEach(li => {
        li.querySelector(".nav-link").classList.remove("active");
      });
      targetTab.classList.add("active");
      const sections = document.querySelectorAll(".section");
      sections.forEach(sec => sec.classList.remove("active"));
      const targetId = targetTab.getAttribute("data-target");
      document.getElementById(targetId).classList.add("active");
      if (targetId === "calendarView") {
        initializeCalendar();
      }
      if (targetId === "listView") {
        renderListView();
      }
    });
    
    // INITIALIZATION
    initializeCalendar();
    renderListView();
    renderEventsTable();
    
  </script>
</body>
</html>
