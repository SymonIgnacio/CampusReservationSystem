import React, { useContext } from 'react';
import { BrowserRouter as Router, Routes, Route, useLocation, Navigate } from 'react-router-dom';
import AuthProvider, { AuthContext } from './context/AuthContext';
import EventProvider from './context/EventContext';
import './App.css';
import LoginPage from './Pages/LoginPage/loginPage';
import RegisterPage from './Pages/RegisterPage/registerPage';
import ClientDashboard from './Pages/Dashboard/clientDashboard';
import AdminDashboard from './Pages/Admin/Dashboard/adminDashboard';
import RequestEvent from './Pages/Request Event/requestEvent';
import Settings from './Pages/Settings/settings';
import Navbar from './Components/Navbar';

// Import admin pages
import AdminEvents from './Pages/Admin/Events/adminEvents';
import AdminCreateEvent from './Pages/Admin/CreateEvent/adminCreateEvent';
import AdminUsers from './Pages/Admin/Users/adminUsers';
import AdminRequests from './Pages/Admin/Requests/adminRequests';
import AdminTransactions from './Pages/Admin/Transactions/adminTransactions';
import ManageFacilities from './Pages/Admin/Facilities/manageFacilities';
import ManageEquipment from './Pages/Admin/Equipment/manageEquipment';
import AddUser from './Pages/Admin/AddUser/addUser';
import ManageUsers from './Pages/Admin/ManageUsers/manageUsers';
import Reports from './Pages/Reports/SimpleReports';

// Import client pages for viewing facilities and equipment
import ViewFacilities from './Pages/Facilities/ViewFacilities';
import ViewEquipment from './Pages/Equipment/ViewEquipment';

const AdminSettings = Settings;

// Protected route component
const ProtectedRoute = ({ element, requiredRole }) => {
  const { user, isAuthenticated } = useContext(AuthContext);
  
  if (!isAuthenticated) {
    return <Navigate to="/" replace />;
  }
  
  if (requiredRole && user?.role !== requiredRole) {
    return <Navigate to="/dashboard" replace />;
  }
  
  return element;
};

function AppContent() {
  const location = useLocation();
  
  // Check if current route is an admin route
  const isAdminRoute = location.pathname.startsWith('/admin');
  
  // Show navbar on these routes
  const clientNavbarRoutes = ['/dashboard', '/requestEvent', '/settings', '/facilities', '/equipment'];
  const adminNavbarRoutes = [
    '/admin/dashboard', 
    '/admin/events', 
    '/admin/requests', 
    '/admin/transactions',
    '/admin/create-event', 
    '/admin/users', 
    '/admin/settings',
    '/admin/manage-facilities',
    '/admin/manage-equipment',
    '/admin/add-user',
    '/admin/manage-users',
    '/admin/reports'
  ];
  
  // Determine if navbar should be shown
  const showNavbar = clientNavbarRoutes.includes(location.pathname) || adminNavbarRoutes.includes(location.pathname);

  return (
    <>
      {showNavbar && <Navbar isAdminPage={isAdminRoute} />}
      
      <div className="app-container">
        <Routes>
          {/* Public routes */}
          <Route path="/" element={<LoginPage />} />
          <Route path="/register" element={<RegisterPage />} />
        
          {/* Client routes */}
          <Route path="/dashboard" element={
            <ProtectedRoute element={<ClientDashboard />} />
          } />
          <Route path="/requestEvent" element={
            <ProtectedRoute element={<RequestEvent />} />
          } />
          <Route path="/settings" element={
            <ProtectedRoute element={<Settings />} />
          } />
          <Route path="/facilities" element={
            <ProtectedRoute element={<ViewFacilities />} />
          } />
          <Route path="/equipment" element={
            <ProtectedRoute element={<ViewEquipment />} />
          } />
        
          {/* Admin routes */}
          <Route path="/admin/dashboard" element={
            <ProtectedRoute element={<AdminDashboard />} requiredRole="admin" />
          } />
          <Route path="/admin/events" element={
            <ProtectedRoute element={<AdminEvents />} requiredRole="admin" />
          } />
          <Route path="/admin/requests" element={
            <ProtectedRoute element={<AdminRequests />} requiredRole="admin" />
          } />
          <Route path="/admin/transactions" element={
            <ProtectedRoute element={<AdminTransactions />} requiredRole="admin" />
          } />
          <Route path="/admin/create-event" element={
            <ProtectedRoute element={<AdminCreateEvent />} requiredRole="admin" />
          } />
          <Route path="/admin/users" element={
            <ProtectedRoute element={<AdminUsers />} requiredRole="admin" />
          } />
          <Route path="/admin/settings" element={
            <ProtectedRoute element={<AdminSettings />} requiredRole="admin" />
          } />
          <Route path="/admin/manage-facilities" element={
            <ProtectedRoute element={<ManageFacilities />} requiredRole="admin" />
          } />
          <Route path="/admin/manage-equipment" element={
            <ProtectedRoute element={<ManageEquipment />} requiredRole="admin" />
          } />
          <Route path="/admin/add-user" element={
            <ProtectedRoute element={<AddUser />} requiredRole="admin" />
          } />
          <Route path="/admin/manage-users" element={
            <ProtectedRoute element={<ManageUsers />} requiredRole="admin" />
          } />
          <Route path="/admin/reports" element={
            <ProtectedRoute element={<Reports isAdmin={true} />} requiredRole="admin" />
          } />
        </Routes>
      </div>
    </>
  );
}

function App() {
  return (
    <AuthProvider>
      <EventProvider>
        <Router>
          <AppContent />
        </Router>
      </EventProvider>
    </AuthProvider>
  );
}

export default App;