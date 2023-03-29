import React from 'react';
import { Route } from 'react-router-dom';
import Login from "../components/Login";
import Home from "../components/Home";

const route = () => {
    return [
        <Route key="home" exact path="/" element={<Home />} />,
        <Route key="login" exact path="/login" element={<Login />} />
    ];
};

export default route;
