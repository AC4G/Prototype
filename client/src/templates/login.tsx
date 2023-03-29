import { Link } from 'react-router-dom'

const login = ({ nickname, password, errors, handleInputChange, handleSubmit }) => (
    <div id="login-box">
        <div id="login-content-box">
            <div className="w-fit mx-auto mt-[50px]">
                <p className="text-[22px]">Sign In</p>
            </div>
            <div className="w-fit h-fit mx-auto mt-[10px]">
                <div className="w-fit h-fit mx-auto">
                    <p className="text-[14px]">Connect to your STEXS account.</p>
                </div>
                <div className="w-fit h-fit flex">
                    <p className="text-[14px]">Don't have an account?&nbsp;</p>
                    <Link to="/registration" className="text-[14px] text-[#4EA8DE] ">Sign up here</Link>
                </div>
            </div>
            {Object.keys(errors).length > 0 && (
                <div className="error-message-box">
                    <ul className="list-disc text-red-400 text-[16px]">
                        {Object.keys(errors).map((key) => (
                            <li className="error-message" key={key}>{errors[key]}</li>
                        ))}
                    </ul>
                </div>
            )}
            <form className="" onSubmit={handleSubmit}>
                <div className="w-fit w-fit mx-auto mt-[40px]">
                    <div className="w-fit h-fit">
                        <p className="text-[18px]">Nickname</p>
                    </div>
                    <input type="text" name="nickname" className="rounded select-none text-white bg-[#343434] w-[330px] h-[40px] pl-[12px] pr-[12px] mt-[12px] focus:outline-none" value={nickname} placeholder="Enter your nickname..." onChange={handleInputChange} />
                    <div className="w-fit h-fit mt-[24px]">
                        <p className="text-[18px]">Password</p>
                    </div>
                    <input type="password" name="password" className="rounded select-none text-white bg-[#343434] w-[330px] h-[40px] pl-[12px] pr-[12px] mt-[12px] focus:outline-none" value={password} placeholder="Enter your password..." onChange={handleInputChange} />
                </div>
                <div className="w-fit h-fit mx-auto mt-[40px]">
                    <button className="w-[80px] h-[35px] rounded bg-[#5E60CE] shadow-md text-white select-none hover:shadow-xl hover:text-black transition" type="submit">Log In</button>
                </div>
            </form>
            <div className="w-fit h-fit mx-auto mt-[30px] mb-[34px]">
                <Link to="/passwordForgotten" className="text-[14px] text-[#4EA8DE]">Forgot your password?</Link>
            </div>
        </div>
    </div>
);

export default login;
