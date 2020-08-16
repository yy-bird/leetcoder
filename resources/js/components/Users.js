import React, {useState, useEffect} from 'react';
import axios from 'axios';

function Users() {
    const [users, setUsers] = useState([]);

    useEffect(() => {
        axios.get("/api/users").then(res => {
            setUsers(res);
        })
    })
    return <h1>{users}</h1>
}

export default Users;