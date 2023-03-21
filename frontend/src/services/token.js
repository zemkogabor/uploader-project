class TokenService {
  getAccessToken() {
    return localStorage.getItem('accessToken')
  }

  getRefreshToken() {
    return localStorage.getItem('refreshToken')
  }

  getAccessTokenExpireAt() {
    return new Date(localStorage.getItem('accessTokenExpireAt'))
  }

  isAccessTokenExpired() {
    // Real expire date - 1 minute
    return this.getAccessTokenExpireAt().getTime() < (new Date()).getTime()
  }

  updateTokens(accessToken, accessTokenExpiresIn, refreshToken) {
    localStorage.setItem('accessToken', accessToken)

    const expire = new Date()
    // - 1 minute from real expire
    expire.setSeconds(expire.getSeconds() + parseInt(accessTokenExpiresIn) - 60)
    localStorage.setItem('accessTokenExpireAt', expire.toISOString())

    localStorage.setItem('refreshToken', refreshToken)
  }

  removeTokens() {
    localStorage.removeItem('accessToken')
    localStorage.removeItem('accessTokenExpireAt')
    localStorage.removeItem('refreshToken')
  }
}

export default new TokenService()
