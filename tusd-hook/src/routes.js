const Axios = require('axios')

module.exports = (app) => {
  app.post('/write', async (req, res) => {
    switch (req.headers['hook-name']) {
      // Authenticate file upload request
      case 'pre-create':
        const originalRequestHeader = req.body['HTTPRequest']['Header']

        // The authorization token must be in the header of the original file upload request.
        if (originalRequestHeader.Authorization === undefined) {
          res.sendStatus(400)
        }

        try {
          // /his token must be validated on the oauth server.
          await Axios.get(process.env.AUTH_URL + '/user', {
            headers: {
              Authorization: originalRequestHeader.Authorization[0]
            }
          })

          res.sendStatus(200)
        } catch (err) {
          if (err.response !== undefined) {
            res.sendStatus(err.response.status)
          } else {
            console.error(err.message)
            res.sendStatus(500)
          }
        }
        break;
      default:
        res.sendStatus(200)
        break
    }
  })

  // Health check endpoint
  app.get('/healthcheck', (req, res) => res.send())

  // Disable any other path.
  app.get('/*', (req, res) => {
    res.statusCode = 404
    res.send()
  })
}
