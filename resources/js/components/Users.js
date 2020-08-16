import React, {useState, useEffect} from 'react';
import Table from 'react-bootstrap/Table';
import axios from 'axios';

function Users() {
    const [users, setUsers] = useState([]);

    useEffect(() => {
        axios.get("/api/users").then(res => {
            console.log(res);
            setUsers(res.data);
        })
    }, []);
    return (<Table className="text-center">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Rank</th>
                        <th>Rating</th>
                        <th>Solved Questions</th>
                        <th>Finished Contests</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {users.map(user => <tr key={user.id}>
                                         <td>{user.id}</td>
                                         <td>{user.username}</td>
                                         <td>{user.global_rank}</td>
                                         <td>{user.rating}</td>
                                         <td>{user.solved_questions}</td>
                                         <td>{user.finished_contests}</td>
                                         <td></td>
                                       </tr>)}
                </tbody>
            </Table>);
}

export default Users;