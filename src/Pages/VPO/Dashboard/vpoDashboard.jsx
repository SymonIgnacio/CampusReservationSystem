import React, { useState, useEffect } from 'react';
import './vpoDashboard.css';

const VPODashboard = () => {
  const [stats, setStats] = useState({
    totalRequests: 0,
    pendingApproval: 0,
    approvedToday: 0,
    upcomingEvents: 0,
    finishedEvents: 0
  });

  const [events, setEvents] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedMonth, setSelectedMonth] = useState('');

  useEffect(() => {
    fetchStats();
    fetchEvents();
  }, []);

  const fetchStats = async () => {
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/stats_with_approved.php');
      const data = await response.json();
      if (data.success || data.status === 'success') {
        setStats({
          totalRequests: data.total_events || 0,
          pendingApproval: data.pending_events || 0,
          approvedToday: data.approved_events || 0,
          upcomingEvents: data.upcoming_events || 0,
          finishedEvents: 0
        });
      }
    } catch (error) {
      console.error('Error fetching stats:', error);
    }
  };

  const fetchEvents = async () => {
    try {
      // VPO should only see approved events for oversight, not pending requests
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/vpo_approved_events.php');
      const data = await response.json();
      if (data.success) {
        setEvents(data.events || []);
      }
    } catch (error) {
      console.error('Error fetching events:', error);
    }
  };

  const getFieldValue = (event, fieldNames) => {
    for (const fieldName of fieldNames) {
      if (event[fieldName] !== undefined) {
        return event[fieldName];
      }
    }
    return 'N/A';
  };

  const searchedEvents = events.filter(event => {
    if (searchTerm !== '') {
      const eventName = getFieldValue(event, ['activity', 'name', 'title', 'event_name']).toLowerCase();
      const eventDate = getFieldValue(event, ['date', 'date_need_from']).toLowerCase();
      const eventTime = getFieldValue(event, ['time', 'start_time']).toLowerCase();
      const eventLocation = getFieldValue(event, ['venue', 'location', 'place']).toLowerCase();
      
      const search = searchTerm.toLowerCase();
      if (!(eventName.includes(search) || eventDate.includes(search) || eventTime.includes(search) || eventLocation.includes(search))) {
        return false;
      }
    }
    
    if (selectedMonth !== '') {
      const eventDate = getFieldValue(event, ['date', 'date_need_from']);
      if (eventDate && eventDate !== 'N/A') {
        const eventMonth = new Date(eventDate).getMonth() + 1;
        if (eventMonth.toString() !== selectedMonth) {
          return false;
        }
      }
    }
    
    return true;
  });

  return (
    <div className="vpo-dashboard">
      <div className="dashboard-header">
        <h1 className="page-title">VPO DASHBOARD - NO CALENDAR VERSION</h1>
      </div>

      <div className="stats-cards">
        <div className="card yellow">
          <h2>{events.length}</h2>
          <p>UPCOMING EVENTS</p>
        </div>
        <div className="card blue">
          <h2>0</h2>
          <p>FOR VPO REVIEW</p>
        </div>
        <div className="card red">
          <h2>0</h2>
          <p>DECLINED</p>
        </div>
        <div className="card green">
          <h2>{stats.approvedToday}</h2>
          <p>APPROVED</p>
        </div>
        <div className="card purple">
          <h2>0</h2>
          <p>FINISHED EVENTS</p>
        </div>
      </div>

      <div className="upcoming-events">
        <h2>UPCOMING EVENTS</h2>
        <div className="events-header">
          <div className="search-container">
            <div className="search-box">
              <input
                type="text"
                placeholder="Search events"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
              />
            </div>
          </div>
          <div className="filter-controls">
            <select 
              value={selectedMonth} 
              onChange={(e) => setSelectedMonth(e.target.value)}
              className="month-filter"
            >
              <option value="">All Months</option>
              <option value="1">January</option>
              <option value="2">February</option>
              <option value="3">March</option>
              <option value="4">April</option>
              <option value="5">May</option>
              <option value="6">June</option>
              <option value="7">July</option>
              <option value="8">August</option>
              <option value="9">September</option>
              <option value="10">October</option>
              <option value="11">November</option>
              <option value="12">December</option>
            </select>
            <button 
              className="refresh-button" 
              onClick={() => {
                fetchStats();
                fetchEvents();
              }}
            >
              Refresh
            </button>
          </div>
        </div>

        {searchedEvents.length > 0 ? (
          <div className="table-container">
            <table>
              <thead>
                <tr>
                  <th>EVENT</th>
                  <th>DATE</th>
                  <th>TIME</th>
                  <th>LOCATION</th>
                </tr>
              </thead>
              <tbody>
                {searchedEvents.map((event, index) => (
                  <tr key={`${getFieldValue(event, ['id', 'request_id'])}-${index}`}>
                    <td>{getFieldValue(event, ['activity', 'name', 'title', 'event_name'])}</td>
                    <td>{getFieldValue(event, ['date'])}</td>
                    <td>{getFieldValue(event, ['time'])}</td>
                    <td>{getFieldValue(event, ['venue', 'location', 'place'])}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <p className="no-events">No approved events found.</p>
        )}
      </div>
    </div>
  );
};

export default VPODashboard;