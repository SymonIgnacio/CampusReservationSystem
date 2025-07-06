import React, { useState, useEffect } from 'react';
import './adminTransactions.css';

function AdminTransactions({ isCollapsed }) {
  const [transactions, setTransactions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [filter, setFilter] = useState('all');

  useEffect(() => {
    fetchTransactions();
  }, []);

  const fetchTransactions = async () => {
    setLoading(true);
    setError(null); // Clear previous errors
    try {
      // Use window.location.origin to dynamically determine the base URL
      const baseUrl = window.location.hostname === 'localhost' ? 
        'http://localhost' : 
        window.location.origin;
      
      const apiUrl = `${baseUrl}/CampusReservationSystem/src/api/transactions.php`;
      console.log("Fetching from:", apiUrl); // Debug log
      
      const response = await fetch(apiUrl, {
        credentials: 'include',
        mode: 'cors'
      });
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      
      const data = await response.json();
      console.log("Transactions data:", data); // Debug log
      
      if (data.success) {
        setTransactions(data.transactions || []);
        if ((data.transactions || []).length === 0) {
          console.log("No transactions returned from API");
        }
      } else {
        throw new Error(data.message || 'Failed to fetch transactions');
      }
    } catch (error) {
      console.error("Error fetching transactions:", error);
      setError('Failed to load transactions. Please try again later.');
    } finally {
      setLoading(false);
    }
  };

  // Format date to be more readable
  const formatDate = (dateString) => {
    try {
      const options = { year: 'numeric', month: 'short', day: 'numeric' };
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', options);
    } catch (error) {
      return dateString || 'N/A'; // Return original string or N/A if parsing fails
    }
  };

  // Filter transactions based on status
  const filteredTransactions = filter === 'all' 
    ? transactions 
    : transactions.filter(transaction => {
        if (filter === 'approved') return transaction.display_status === 'approved';
        if (filter === 'declined') return transaction.display_status === 'declined';
        return transaction.display_status === filter;
      });

  // Get status badge class
  const getStatusBadgeClass = (status) => {
    switch (status) {
      case 'approved': return 'status-badge approved';
      case 'declined': return 'status-badge rejected';
      default: return 'status-badge';
    }
  };

  return (
    <div className={`transactions-container ${isCollapsed ? 'collapsed' : ''}`}>
      <main className="main-content">
        <div className="transactions-header">
          <h1 className="page-title">TRANSACTIONS</h1>
          <div className="filter-controls">
            <select 
              value={filter} 
              onChange={(e) => setFilter(e.target.value)}
              className="filter-dropdown"
            >
              <option value="all">All Transactions</option>
              <option value="approved">Approved</option>
              <option value="declined">Declined</option>
            </select>
            <button 
              className="refresh-button" 
              onClick={fetchTransactions}
              disabled={loading}
            >
              {loading ? 'Loading...' : 'Refresh'}
            </button>
          </div>
        </div>

        {error && (
          <div className="error-message">
            <p>{error}</p>
          </div>
        )}
        
        {loading ? (
          <p className="loading-message">Loading transactions...</p>
        ) : filteredTransactions.length > 0 ? (
          <div className="table-container">
            <table>
              <thead>
                <tr>
                  <th>REFERENCE NO.</th>
                  <th>ACTIVITY</th>
                  <th>REQUESTER</th>
                  <th>VENUE</th>
                  <th>START DATE</th>
                  <th>END DATE</th>
                  <th>STATUS</th>
                </tr>
              </thead>
              <tbody>
                {filteredTransactions.map((transaction, index) => (
                  <tr key={index}>
                    <td>{transaction.reference_number || 'N/A'}</td>
                    <td>{transaction.event_name || transaction.activity || 'N/A'}</td>
                    <td>{transaction.request_by || `${transaction.firstname || ''} ${transaction.lastname || ''}`.trim() || transaction.requestor_name || 'N/A'}</td>
                    <td>{transaction.venue || transaction.resource_name || 'N/A'}</td>
                    <td>{formatDate(transaction.date_need_from || transaction.start_time)}</td>
                    <td>{formatDate(transaction.date_need_until || transaction.end_time)}</td>
                    <td>
                      <span className={getStatusBadgeClass(transaction.display_status)}>
                        {transaction.display_status.toUpperCase()}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <p className="no-transactions">No transactions found.</p>
        )}
      </main>
    </div>
  );
}

export default AdminTransactions;