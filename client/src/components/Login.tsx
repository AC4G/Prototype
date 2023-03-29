import React, { useState } from 'react'
import login from "../templates/login";
import config from '../config';

const Login = (props) => {
    const [state, setState] = useState({
        nickname: '',
        password: '',
        errors: {},
    });

    const handleInputChange = (event) => {
        const { name, value } = event.target;
        setState((prevState) => ({ ...prevState, [name]: value }));
    };

    const handleSubmit = (event) => {
        event.preventDefault();

        // perform validation checks
        const { nickname, password } = state;
        const errors = {};

        if (!nickname) {
            errors.nickname = 'Nickname is required';
        }

        if (!password) {
            errors.password = 'Password is required';
        }

        if (Object.keys(errors).length > 0) {
            setState((prevState) => ({ ...prevState, errors }));
            return;
        }

        const url = `${config.BACKEND_API_URL}/login`; // use the BACKEND_API_URL constant
        const data = { nickname, password };
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
        })
            .then(response => response.json())
            .then(data => {

            })
            .catch(error => {
                // handle any errors that occur during the network request
            });
    };

    const { nickname, password, errors } = state;
    return login({
        nickname,
        password,
        errors,
        handleInputChange,
        handleSubmit,
        ...props,
    });
};

export default Login;
