import React from 'react';
import ReactDOM from 'react-dom';
import {Container} from 'react-bootstrap';
import {
    BrowserRouter as Router,
    Switch,
    Route,
  } from "react-router-dom";

import Navigation  from './Navigation';
import Users from './Users';

function App() {
    return (
        <Container>
            <Router>
                <Navigation />
                <Switch>
                    <Route path="/users">
                        <Users />
                    </Route>
                </Switch>
            </Router>
        </Container>
    );
}

export default App;

if (document.getElementById('app')) {
    ReactDOM.render(<App />, document.getElementById('app'));
}
