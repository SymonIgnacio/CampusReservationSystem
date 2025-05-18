import { useState, useContext } from 'react';
import { useNavigate } from 'react-router-dom';
import { AuthContext } from '../context/AuthContext';
import './Navbar.css';

const Navbar = ({ isAdminPage = false }) => {
    const [isCollapsed, setIsCollapsed] = useState(false);
    const { user, logout } = useContext(AuthContext);
    const navigate = useNavigate();

    const toggleSidebar = () => {
        setIsCollapsed(!isCollapsed);
    };

    // Helper function to get user's name
    const getUserName = () => {
        if (!user) return 'Guest';
        
        // Try different field names that might contain the user's name
        if (user.firstname && user.lastname) {
            return `${user.firstname} ${user.lastname}`;
        } else if (user.name) {
            return user.name;
        } else if (user.username) {
            return user.username;
        }
        return 'User';
    };

    // Helper function to get user's role
    const getUserRole = () => {
        if (!user) return 'Guest';
        return user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'User';
    };

    // Handle navigation
    const handleNavigation = (path) => (e) => {
        e.preventDefault();
        navigate(path);
    };

    // Handle logout
    const handleLogout = async (e) => {
        e.preventDefault();
        await logout();
        navigate('/');
    };

    return (
        <div className={`sidebar ${isCollapsed ? 'collapsed' : ''}`}>
            <div className="top">
                <div className="logo">
                    <span className="logo-text">Campus Reservation</span>
                </div>
                <i className="bx bx-menu" id="btn" onClick={toggleSidebar}></i>
            </div>

            <div className='userProfile'>
                <div className="userImg">
                    <img src="/images/userProfile.png" alt="User" className="user-img" />
                </div>
                <div className="userName">
                    <p className="user-name">{getUserName()}</p>
                    <p className="user-role">{getUserRole()}</p>
                </div>
            </div>

            <ul className="nav-list">
                {isAdminPage ? (
                    // Admin Navigation Items
                    <>
                        <li>
                            <a href="#" onClick={handleNavigation('/admin/dashboard')}>
                                <i className="bx bxs-dashboard"></i>
                                <span className="link-text">DASHBOARD</span>
                            </a>
                            <span className="tooltip">Dashboard</span>
                        </li>
                        <li>
                            <a href="#" onClick={handleNavigation('/admin/events')}>
                                <i className="bx bx-calendar"></i>
                                <span className="link-text">EVENTS</span>
                            </a>
                            <span className="tooltip">Events</span>
                        </li>
                        <li>
                            <a href="#" onClick={handleNavigation('/admin/requests')}>
                                <i className="bx bx-edit"></i>
                                <span className="link-text">REQUESTS</span>
                            </a>
                            <span className="tooltip">Requests</span>
                        </li>
                        <li>
                            <a href="#" onClick={handleNavigation('/admin/create-event')}>
                                <i className="bx bx-plus-square"></i>
                                <span className="link-text">CREATE EVENT</span>
                            </a>
                            <span className="tooltip">Create Event</span>
                        </li>
                        <li>
                            <a href="#" onClick={handleNavigation('/admin/users')}>
                                <i className="bx bx-user"></i>
                                <span className="link-text">USERS</span>
                            </a>
                            <span className="tooltip">Users</span>
                        </li>
                        <li>
                            <a href="#" onClick={handleNavigation('/admin/settings')}>
                                <i className="bx bx-cog"></i>
                                <span className="link-text">SETTINGS</span>
                            </a>
                            <span className="tooltip">Settings</span>
                        </li>
                    </>
                ) : (
                    // Regular User Navigation Items
                    <>
                        <li>
                            <a href="#" onClick={handleNavigation('/dashboard')}>
                                <i className="bx bxs-grid-alt"></i>
                                <span className="link-text">DASHBOARD</span>
                            </a>
                            <span className="tooltip">Dashboard</span>
                        </li>
                        <li>
                            <a href="#" onClick={handleNavigation('/requestEvent')}>
                                <i className="bx bx-calendar-event"></i>
                                <span className="link-text">REQUEST EVENT</span>
                            </a>
                            <span className="tooltip">Request Event</span>
                        </li>
                        <li>
                            <a href="#" onClick={handleNavigation('/settings')}>
                                <i className="bx bx-cog"></i>
                                <span className="link-text">SETTINGS</span>
                            </a>
                            <span className="tooltip">Settings</span>
                        </li>
                    </>
                )}
                
                <li>
                    <a href="#" onClick={handleLogout}>
                        <i className="bx bx-log-out"></i>
                        <span className="link-text">LOGOUT</span>
                    </a>
                    <span className="tooltip">Logout</span>
                </li>
            </ul>
        </div>
    );
}

export default Navbar;