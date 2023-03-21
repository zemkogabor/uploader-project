const express = require('express')
const http = require('http')
const app = express()
const createHttpRoutes = require('./routes')
const gracefulShutdown = require('http-graceful-shutdown')
const httpServer = http.createServer(app)
const port = process.env.PORT || 3000

// This ensures the request IP matches the client and not the load-balancer.
app.enable('trust proxy')
app.use(express.json())

// HTTP Routes
createHttpRoutes(app)

httpServer.listen(port, () => {
  console.log(`Server running at http://127.0.0.1:${port}/`)
})

// Handle SIGINT or SIGTERM and drain connections.
gracefulShutdown(httpServer)
