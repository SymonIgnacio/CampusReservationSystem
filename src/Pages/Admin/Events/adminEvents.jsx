import React, { useState, useContext, useEffect } from 'react';
import { EventContext } from '../../../context/EventContext';
import 'boxicons/css/boxicons.min.css';
import './adminEvents.css';

function AdminEvents({ isCollapsed }) {
  const { events, loading, error, refreshData } = useContext(EventContext);
  const [date] = useState(new Date());
  const [month, setMonth] = useState(date.getMonth());
  const [year, setYear] = useState(date.getFullYear());
  const [searchTerm, setSearchTerm] = useState('');
  const [goToDate, setGoToDate] = useState('');
  const [showGoToModal, setShowGoToModal] = useState(false);
  const [goToMonth, setGoToMonth] = useState('');
  const [goToDay, setGoToDay] = useState('');
  const [goToYear, setGoToYear] = useState('');
  const [filteredEvents, setFilteredEvents] = useState([]);

  const months = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
  ];

  const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  const shortDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

  // Refresh data when component mounts
  useEffect(() => {
    refreshData();
  }, []);

  // Update filtered events when events change - ONLY SHOW APPROVED EVENTS
  useEffect(() => {
    if (events && events.length > 0) {
      // Only show approved events in the calendar
      const approvedEvents = events.filter(event => event.status === 'approved');
      setFilteredEvents(approvedEvents);
    }
  }, [events]);

  const renderCalendar = () => {
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();

    let dates = [];
    const totalCells = 7 * 6; // 7 days x 6 weeks

    // Previous month days
    for (let i = 0; i < firstDay; i++) {
      dates.push({
        day: daysInPrevMonth - firstDay + i + 1,
        currentMonth: false,
        isToday: false
      });
    }

    // Current month days
    for (let i = 1; i <= daysInMonth; i++) {
      const isToday = 
        i === date.getDate() && 
        month === new Date().getMonth() && 
        year === new Date().getFullYear();
      
      dates.push({
        day: i,
        currentMonth: true,
        isToday: isToday
      });
    }

    // Next month days (to fill remaining cells)
    const remainingCells = totalCells - dates.length;
    for (let i = 1; i <= remainingCells; i++) {
      dates.push({
        day: i,
        currentMonth: false,
        isToday: false
      });
    }

    // Split into weeks (6 weeks)
    let rows = [];
    for (let i = 0; i < totalCells; i += 7) {
      rows.push(dates.slice(i, i + 7));
    }

    return rows.map((week, weekIndex) => (
      <tr key={`week-${weekIndex}`}>
        {week.map((dayObj, dayIndex) => (
          <td 
            key={`day-${weekIndex}-${dayIndex}`}
            className={`${!dayObj.currentMonth ? 'inactive' : ''} ${dayObj.isToday ? 'today' : ''}`}
          >
            {dayObj.day}
          </td>
        ))}
      </tr>
    ));
  };

  const handleNavClick = (direction) => {
    let newMonth = month;
    let newYear = year;

    if (direction === "prev") {
      if (month === 0) {
        newYear--;
        newMonth = 11;
      } else {
        newMonth--;
      }
    } else {
      if (month === 11) {
        newYear++;
        newMonth = 0;
      } else {
        newMonth++;
      }
    }

    setMonth(newMonth);
    setYear(newYear);
  };

  const handleSearch = (e) => {
    e.preventDefault();
    if (!searchTerm.trim()) {
      // Reset to only approved events
      const approvedEvents = events.filter(event => event.status === 'approved');
      setFilteredEvents(approvedEvents);
      return;
    }
    
    // Search only within approved events
    const filtered = events.filter(event => {
      const title = event.name || event.title || event.activity || '';
      return event.status === 'approved' && title.toLowerCase().includes(searchTerm.toLowerCase());
    });
    setFilteredEvents(filtered);
  };
  
  const handleModalGoTo = (e) => {
    e.preventDefault();
    if (!goToMonth || !goToYear) return;
    
    const month = parseInt(goToMonth) - 1;
    const year = parseInt(goToYear);
    
    if (!isNaN(month) && !isNaN(year) && month >= 0 && month <= 11) {
      setMonth(month);
      setYear(year);
      setShowGoToModal(false);
      setGoToMonth('');
      setGoToDay('');
      setGoToYear('');
    }
  };

  const handleTextGoTo = (e) => {
    e.preventDefault();
    if (!goToDate) return;
    
    const dateParts = goToDate.split('-');
    if (dateParts.length === 2) {
      const month = parseInt(dateParts[0]) - 1;
      const year = parseInt(dateParts[1]);
      
      if (!isNaN(month) && !isNaN(year) && month >= 0 && month <= 11) {
        setMonth(month);
        setYear(year);
        setGoToDate('');
      }
    } else if (dateParts.length === 3) {
      const month = parseInt(dateParts[0]) - 1;
      const year = parseInt(dateParts[2]);
      
      if (!isNaN(month) && !isNaN(year) && month >= 0 && month <= 11) {
        setMonth(month);
        setYear(year);
        setGoToDate('');
      }
    }
  };
  
  // Helper function to get event date
  const getEventDate = (event) => {
    try {
      // Try different date fields
      const dateStr = event.date || event.date_from || '';
      if (!dateStr) return null;
      
      return new Date(dateStr);
    } catch (e) {
      console.error('Error parsing date:', e);
      return null;
    }
  };
  
  // Filter events for current month
  const currentMonthEvents = filteredEvents.filter(event => {
    const eventDate = getEventDate(event);
    return eventDate && 
           eventDate.getMonth() === month && 
           eventDate.getFullYear() === year;
  });

  // Separate upcoming and finished events
  const upcomingEvents = currentMonthEvents.filter(event => {
    const eventDate = getEventDate(event);
    return eventDate && eventDate >= new Date();
  });

  const finishedEvents = currentMonthEvents.filter(event => {
    const eventDate = getEventDate(event);
    return eventDate && eventDate < new Date();
  });

  // Format date for display
  const formatEventDate = (event) => {
    const eventDate = getEventDate(event);
    if (!eventDate) return 'Date TBD';
    
    return `${months[eventDate.getMonth()].slice(0, 3)} ${eventDate.getDate()}`;
  };

  return (
    <div className={`dashboard-container ${isCollapsed ? 'collapsed' : ''}`}>
      <div className="calendar-container">
        <h1 className="page-title">EVENTS CALENDAR</h1>
        <p className="subtitle">Showing approved events only</p>
        
        <div className="calendar-nav">
          <button onClick={() => handleNavClick("prev")} className="nav-button">
            <i className='bx bx-chevron-left'></i>
          </button>
          <h2>{months[month].toUpperCase()} {year}</h2>
          <button onClick={() => handleNavClick("next")} className="nav-button">
            <i className='bx bx-chevron-right'></i>
          </button>
        </div>
        <div className='search-container'>
          <button className='btn-goto' onClick={() => setShowGoToModal(true)}>
            <span>GO TO</span>
          </button>
          <form onSubmit={handleSearch} className="btn-search-container">
            <input 
              type="text" 
              placeholder="Search events..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="search-input"
            />
            <button type="submit" className='btn-search'>
              <i className="bx bx-search"></i>
            </button>
          </form>
        </div>

        <table className="calendar-table">
          <thead>
            <tr>
              {shortDays.map(day => (
                <th key={day}>{day.toUpperCase()}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {renderCalendar()}
          </tbody>
        </table>
      </div>

      <div className="event-planner">
        <div className="legend">
          <h4 className='dashboard-label'>LEGEND</h4>
          <div className="border">
            <button className="upcoming"> UPCOMING </button>
            <button className="finished"> FINISHED </button>
          </div>
        </div>
        <div className="events">
          <h4 className='dashboard-label'>UPCOMING EVENTS FOR <span className='dashboard-label'>{months[month].toUpperCase()} {year}</span></h4>
          <div className="border">
            {loading ? (
              <p className="loading-message">Loading events...</p>
            ) : error ? (
              <p className="error-message">{error}</p>
            ) : upcomingEvents.length > 0 ? (
              <ul className="event-list">
                {upcomingEvents.map(event => (
                  <li key={event.id || `event-${Math.random()}`} className="event-item upcoming">
                    <span className="event-date">
                      {formatEventDate(event)}
                    </span>
                    <br />
                    <span className="event-title">{event.name || event.title || event.activity || 'Untitled Event'}</span>
                  </li>
                ))}
              </ul>
            ) : (
              <p className="no-events">No upcoming events this month</p>
            )}
          </div>
          <h4 className='dashboard-label'>FINISHED EVENTS</h4>
          <div className="border">
            {loading ? (
              <p className="loading-message">Loading events...</p>
            ) : error ? (
              <p className="error-message">{error}</p>
            ) : finishedEvents.length > 0 ? (
              <ul className="event-list">
                {finishedEvents.map(event => (
                  <li key={event.id || `event-${Math.random()}`} className="event-item finished">
                    <span className="event-date">
                      {formatEventDate(event)}
                    </span>
                    <span className="event-title">{event.name || event.title || event.activity || 'Untitled Event'}</span>
                  </li>
                ))}
              </ul>
            ) : (
              <p className="no-events">No finished events this month</p>
            )}
          </div>
        </div>
      </div>

      {/* Go To Modal */}
      {showGoToModal && (
        <div className="modal-overlay">
          <div className="modal-content">
            <h3>GO TO</h3>
            <div className="modal-input-group">
              <label>MONTH</label>
              <input 
                type="number" 
                min="1" 
                max="12" 
                value={goToMonth}
                onChange={(e) => setGoToMonth(e.target.value)}
                placeholder="MM"
              />
            </div>
            <div className="modal-input-group">
              <label>DAY</label>
              <input 
                type="number" 
                min="1" 
                max="31" 
                value={goToDay}
                onChange={(e) => setGoToDay(e.target.value)}
                placeholder="DD"
              />
            </div>
            <div className="modal-input-group">
              <label>YEAR</label>
              <input 
                type="number" 
                min="2000" 
                max="2100" 
                value={goToYear}
                onChange={(e) => setGoToYear(e.target.value)}
                placeholder="YYYY"
              />
            </div>
            <div className="modal-buttons">
              <button 
                className="modal-ok" 
                onClick={handleModalGoTo}
                disabled={!goToMonth || !goToYear}
              >
                OK
              </button>
              <button 
                className="modal-cancel" 
                onClick={() => setShowGoToModal(false)}
              >
                CANCEL
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default AdminEvents;