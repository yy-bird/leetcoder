import React from 'react';
import { LinkContainer } from 'react-router-bootstrap'
import {Navbar, Nav, Form, FormControl, Button} from 'react-bootstrap';

function Navigation(){
    return  <Navbar bg="dark" variant="dark">
                <Navbar.Brand href="#home">Leetcoder</Navbar.Brand>
                <Nav className="mr-auto">
                <LinkContainer to="/users">
                    <Nav.Link>Users</Nav.Link>
                </LinkContainer>
                <Nav.Link href="#features">Features</Nav.Link>
                <Nav.Link href="#pricing">Pricing</Nav.Link>
                </Nav>
                <Form inline>
                <FormControl type="text" placeholder="Search" className="mr-sm-2" />
                <Button variant="outline-info">Search</Button>
                </Form>
            </Navbar>
}

export default Navigation;