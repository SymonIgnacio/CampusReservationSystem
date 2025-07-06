import React, { useState, useEffect } from 'react';
import './approverTransactions.css';

const API_BASE_URL = 'http://localhost/CampusReservationSystem/src/api';

const ApproverTransactions = () => {
  const [transactions, setTransactions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState('all');

  useEffect(() => {
    fetchTransactions();
  }, []);

  const fetchTransactions = async () => {
    try {
      // Fetch approved requests
      const approvedResponse = await fetch(`${API_BASE_URL}/get_approved_requests.php`);
      const approvedData = await approvedResponse.json();
      
      // Fetch declined requests
      const declinedResponse = await fetch(`${API_BASE_URL}/get_declined_requests.php`);
      const declinedData = await declinedResponse.json();
      
      const allTransactions = [];
      
      if (approvedData.success) {
        const approved = approvedData.events.map(event => ({
          ...event,
          status: 'approved',
          action_date: event.approved_at,
          action_by: event.approved_by
        }));
        allTransactions.push(...approved);
      }
      
      if (declinedData.success) {
        const declined = declinedData.requests.map(request => ({
          ...request,
          status: 'declined',
          action_date: request.rejected_at,
          action_by: request.rejected_by
        }));
        allTransactions.push(...declined);
      }
      
      // Sort by action date (newest first)
      allTransactions.sort((a, b) => new Date(b.action_date) - new Date(a.action_date));
      
      setTransactions(allTransactions);
      setLoading(false);
    } catch (error) {
      console.error('Error fetching transactions:', error);
      setLoading(false);
    }
  };

  const filteredTransactions = transactions.filter(transaction => {
    if (filter === 'all') return true;
    return transaction.status === filter;
  });

  const formatTime = (timeStr) => {
    if (!timeStr) return '';
    const [hours, minutes] = timeStr.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const formattedHour = hour % 12 || 12;
    return `${formattedHour}:${minutes} ${ampm}`;
  };

  return (
    <div className="approver-transactions">
      <div className="transactions-header">
        <h1 className="page-title">TRANSACTIONS</h1>
        <div className="filter-buttons">
          <button 
            className={filter === 'all' ? 'active' : ''}
            onClick={() => setFilter('all')}
          >
            All
          </button>
          <button 
            className={filter === 'approved' ? 'active' : ''}
            onClick={() => setFilter('approved')}
          >
            Approved
          </button>
          <button 
            className={filter === 'declined' ? 'active' : ''}
            onClick={() => setFilter('declined')}
          >
            Declined
          </button>
        </div>
      </div>

      {loading ? (
        <p>Loading transactions...</p>
      ) : filteredTransactions.length > 0 ? (
        <div className="transactions-list">
          {filteredTransactions.map(transaction => (
            <div key={`${transaction.status}-${transaction.id}`} className={`transaction-card ${transaction.status}`}>
              <div className="transaction-header">
                <h3>{transaction.activity}</h3>
                <div className="transaction-status">
                  <span className={`status-badge ${transaction.status}`}>
                    {transaction.status.toUpperCase()}
                  </span>
                  <span className="action-date">
                    {new Date(transaction.action_date).toLocaleDateString()}
                  </span>
                </div>
              </div>
              
              <div className="transaction-details">
                <div className="detail-row">
                  <span className="label">Reference:</span>
                  <span className="value">{transaction.reference_number}</span>
                </div>
                <div className="detail-row">
                  <span className="label">Requested by:</span>
                  <span className="value">{transaction.request_by}</span>
                </div>
                <div className="detail-row">
                  <span className="label">Department:</span>
                  <span className="value">{transaction.department_organization}</span>
                </div>
                <div className="detail-row">
                  <span className="label">Date:</span>
                  <span className="value">
                    {new Date(transaction.date_need_from).toLocaleDateString()} - {new Date(transaction.date_need_until).toLocaleDateString()}
                  </span>
                </div>
                <div className="detail-row">
                  <span className="label">Time:</span>
                  <span className="value">
                    {formatTime(transaction.start_time)} - {formatTime(transaction.end_time)}
                  </span>
                </div>
                <div className="detail-row">
                  <span className="label">Venue:</span>
                  <span className="value">{transaction.venue}</span>
                </div>
                <div className="detail-row">
                  <span className="label">Action by:</span>
                  <span className="value">{transaction.action_by}</span>
                </div>
                {transaction.status === 'declined' && transaction.reason && (
                  <div className="detail-row">
                    <span className="label">Reason:</span>
                    <span className="value">{transaction.reason}</span>
                  </div>
                )}
              </div>
            </div>
          ))}
        </div>
      ) : (
        <p>No transactions found.</p>
      )}
    </div>
  );
};

export default ApproverTransactions;