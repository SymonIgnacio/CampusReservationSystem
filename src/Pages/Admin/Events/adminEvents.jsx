import React, { useState } from 'react';
import AdminCreateEvent from '../CreateEvent/adminCreateEvent';
import AdminTransactions from '../Transactions/adminTransactions';
import AdminRequests from '../Requests/adminRequests';
import AdminEventsCalendar from './AdminEventsCalendar';
import './adminEvents.css';

function AdminEvents({ isCollapsed }) {
  const [activeTab, setActiveTab] = useState('create');

  const renderTabContent = () => {
    switch (activeTab) {
      case 'create':
        return <AdminCreateEvent />;
      case 'requests':
        return <AdminRequests />;
      case 'transactions':
        return <AdminTransactions isCollapsed={isCollapsed} />;
      case 'calendar':
        return <AdminEventsCalendar isCollapsed={isCollapsed} />;
      default:
        return <AdminCreateEvent />;
    }
  };

  return (
    <div className="admin-events-container">
      <h1 className="admin-event-page-title">EVENTS</h1>
      
      <div className="events-tabs">
        <button 
          className={`tab ${activeTab === 'create' ? 'active' : ''}`}
          onClick={() => setActiveTab('create')}
        >
          Create Event
        </button>
        <button 
          className={`tab ${activeTab === 'requests' ? 'active' : ''}`}
          onClick={() => setActiveTab('requests')}
        >
          Requests
        </button>
        <button 
          className={`tab ${activeTab === 'transactions' ? 'active' : ''}`}
          onClick={() => setActiveTab('transactions')}
        >
          Transactions
        </button>
        <button 
          className={`tab ${activeTab === 'calendar' ? 'active' : ''}`}
          onClick={() => setActiveTab('calendar')}
        >
          Calendar
        </button>
      </div>
      
      <div className="tab-content">
        {renderTabContent()}
      </div>
    </div>
  );
}

export default AdminEvents;