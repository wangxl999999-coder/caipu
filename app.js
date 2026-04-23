App({
  globalData: {
    userInfo: null,
    token: '',
    baseUrl: 'http://localhost/caipu/public/index.php/api'
  },

  onLaunch() {
    const token = wx.getStorageSync('token')
    const userInfo = wx.getStorageSync('userInfo')
    if (token && userInfo) {
      this.globalData.token = token
      this.globalData.userInfo = userInfo
    }
  },

  request(url, data = {}, method = 'GET') {
    return new Promise((resolve, reject) => {
      wx.showLoading({
        title: '加载中...',
        mask: true
      })

      wx.request({
        url: this.globalData.baseUrl + url,
        data: data,
        method: method,
        header: {
          'Content-Type': 'application/json',
          'token': this.globalData.token
        },
        success: (res) => {
          wx.hideLoading()
          if (res.statusCode === 200) {
            if (res.data.code === 200) {
              resolve(res.data)
            } else if (res.data.code === 401) {
              this.globalData.token = ''
              this.globalData.userInfo = null
              wx.removeStorageSync('token')
              wx.removeStorageSync('userInfo')
              wx.showModal({
                title: '提示',
                content: '登录已过期，请重新登录',
                showCancel: false,
                success: () => {
                  wx.switchTab({
                    url: '/pages/mine/mine'
                  })
                }
              })
              reject(res.data)
            } else {
              wx.showToast({
                title: res.data.msg || '请求失败',
                icon: 'none'
              })
              reject(res.data)
            }
          } else {
            wx.showToast({
              title: '网络错误',
              icon: 'none'
            })
            reject(res)
          }
        },
        fail: (err) => {
          wx.hideLoading()
          wx.showToast({
            title: '网络连接失败',
            icon: 'none'
          })
          reject(err)
        }
      })
    })
  },

  login() {
    return new Promise((resolve, reject) => {
      wx.login({
        success: (res) => {
          if (res.code) {
            wx.getUserProfile({
              desc: '用于完善用户资料',
              success: (userRes) => {
                this.request('/user/login', {
                  code: res.code,
                  nickname: userRes.userInfo.nickName,
                  avatar: userRes.userInfo.avatarUrl,
                  gender: userRes.userInfo.gender
                }, 'POST').then((response) => {
                  this.globalData.token = response.data.token
                  this.globalData.userInfo = response.data.user
                  wx.setStorageSync('token', response.data.token)
                  wx.setStorageSync('userInfo', response.data.user)
                  resolve(response.data)
                }).catch((err) => {
                  reject(err)
                })
              },
              fail: () => {
                this.request('/user/login', {
                  code: res.code
                }, 'POST').then((response) => {
                  this.globalData.token = response.data.token
                  this.globalData.userInfo = response.data.user
                  wx.setStorageSync('token', response.data.token)
                  wx.setStorageSync('userInfo', response.data.user)
                  resolve(response.data)
                }).catch((err) => {
                  reject(err)
                })
              }
            })
          } else {
            reject(new Error('登录失败'))
          }
        },
        fail: (err) => {
          reject(err)
        }
      })
    })
  },

  isLogin() {
    return !!this.globalData.token
  },

  checkLogin() {
    if (!this.isLogin()) {
      wx.showModal({
        title: '提示',
        content: '您还未登录，是否前往登录？',
        success: (res) => {
          if (res.confirm) {
            wx.switchTab({
              url: '/pages/mine/mine'
            })
          }
        }
      })
      return false
    }
    return true
  }
})
