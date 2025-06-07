import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import AuthContext from './context/AuthContext';
import EventContext from './context/EventContext';
import Login from './Pages/Login/login';
import Dashboard from './Pages/Dashboard/dashboard';
import AdminDashboard from './Pages/Admin/Dashboard/adminDashboard';
import AdminRequests from './Pages/Admin/Requests/adminRequests';
import AdminCreateEvent from './Pages/Admin/CreateEvent/adminCreateEvent';
import ApprovedEventsPage from './Pages/Admin/Events/ApprovedEventsPage';
import ApprovedEventsDebugPage from './Pages/Admin/Events/ApprovedEventsDebugPage';
import TransactionsPage from './Pages/Admin/Transactions/TransactionsPage';
import RequestEvent from './Pages/Request Event/requestEvent';
import ProtectedRoute from './components/ProtectedRoute';
import './App.css';

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/admin/requests" element={<AdminRequests />} />
        <Route path="/admin/approved-events" element={<ApprovedEventsPage />} />
        <Route path="/admin/approved-events-debug" element={<ApprovedEventsDebugPage />} />
        <Route path="/admin/transactions" element={<TransactionsPage />} />
        <Route path="*" element={<div>Loading...</div>} />
      </Routes>
    </Router>
  );
}

export default App;