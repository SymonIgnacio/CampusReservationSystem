import { useState, useContext } from 'react';
import { useNavigate } from 'react-router-dom';
import { AuthContext } from '../context/AuthContext';
import './Navbar.css';

const Navbar = ({ isAdminPage = false, isApproverPage = false }) => {
    const [showSettingsMenu, setShowSettingsMenu] = useState(false);
    const [showUsersMenu, setShowUsersMenu] = useState(false);
    const [showResourcesMenu, setShowResourcesMenu] = useState(false);
    const { user, logout } = useContext(AuthContext);
    const navigate = useNavigate();

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
        <div className="sidebar">
            <div className="top">
                <div className="logo">
                    <span className="logo-text">Campus Reservation</span>
                </div>
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
                            <a href="#" onClick={handleNavigation('/admin/reports')}>
                                <i className="bx bx-bar-chart-alt-2"></i>
                                <span className="link-text">REPORTS</span>
                            </a>
                            <span className="tooltip">Reports</span>
                        </li>
                        <li>
                            <a href="#" onClick={(e) => {
                                e.preventDefault();
                                setShowUsersMenu(!showUsersMenu);
                            }}>
                                <i className="bx bx-user"></i>
                                <span className="link-text">USERS</span>
                            </a>
                            <span className="tooltip">Users</span>
                        </li>
                        {showUsersMenu && (
                            <>
                                <li className="submenu-item">
                                    <a href="#" onClick={handleNavigation('/admin/add-user')}>
                                        <i className="bx bx-user-plus"></i>
                                        <span className="link-text">ADD USER</span>
                                    </a>
                                    <span className="tooltip">Add User</span>
                                </li>
                                <li className="submenu-item">
                                    <a href="#" onClick={handleNavigation('/admin/manage-users')}>
                                        <i className="bx bx-user-x"></i>
                                        <span className="link-text">MANAGE USERS</span>
                                    </a>
                                    <span className="tooltip">Manage Users</span>
                                </li>
                            </>
                        )}
                        <li>
                            <a href="#" onClick={(e) => {
                                e.preventDefault();
                                setShowSettingsMenu(!showSettingsMenu);
                            }}>
                                <i className="bx bx-cog"></i>
                                <span className="link-text">SETTINGS</span>
                            </a>
                            <span className="tooltip">Settings</span>
                        </li>
                        {showSettingsMenu && (
                            <>
                                <li className="submenu-item">
                                    <a href="#" onClick={handleNavigation('/admin/manage-facilities')}>
                                        <i className="bx bx-building"></i>
                                        <span className="link-text">MANAGE FACILITIES</span>
                                    </a>
                                    <span className="tooltip">Manage Facilities</span>
                                </li>
                                <li className="submenu-item">
                                    <a href="#" onClick={handleNavigation('/admin/manage-equipment')}>
                                        <i className="bx bx-wrench"></i>
                                        <span className="link-text">MANAGE EQUIPMENT</span>
                                    </a>
                                    <span className="tooltip">Manage Equipment</span>
                                </li>
                            </>
                        )}
                    </>
                ) : isApproverPage ? (
                    // Approver Navigation Items
                    <>
                        <li>
                            <a href="#" onClick={handleNavigation('/approver/dashboard')}>
                                <i className="bx bxs-dashboard"></i>
                                <span className="link-text">DASHBOARD</span>
                            </a>
                            <span className="tooltip">Dashboard</span>
                        </li>
                        <li>
                            <a href="#" onClick={handleNavigation('/approver/events')}>
                                <i className="bx bx-calendar"></i>
                                <span className="link-text">EVENTS</span>
                            </a>
                            <span className="tooltip">Events</span>
                        </li>
                        <li>
                            <a href="#" onClick={handleNavigation('/approver/requests')}>
                                <i className="bx bx-edit"></i>
                                <span className="link-text">REQUESTS</span>
                            </a>
                            <span className="tooltip">Requests</span>
                        </li>
                        <li>
                            <a href="#" onClick={handleNavigation('/approver/transactions')}>
                                <i className="bx bx-transfer"></i>
                                <span className="link-text">TRANSACTIONS</span>
                            </a>
                            <span className="tooltip">Transactions</span>
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