import { Link } from 'react-router-dom'

const home = () => (
    <div>
        <h1>Home</h1>
        <Link to="/login">Login</Link>
    </div>
);

export default home;
