import { useState } from 'react'
import { Routes } from 'react-router-dom'
import route from "./routes/route";

function App() {
  return (
    <Routes>
      {route()}
    </Routes>
  )
}

export default App
