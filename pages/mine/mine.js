const app = getApp()

Page({
  data: {
    userInfo: null,
    isLogin: false,
    statistics: {
      recipe_count: 0,
      favorite_count: 0,
      like_count: 0
    }
  },

  onLoad() {
    this.checkLoginStatus()
  },

  onShow() {
    if (typeof this.getTabBar === 'function' && this.getTabBar()) {
      this.getTabBar().setData({
        selected: 2
      })
    }
    this.checkLoginStatus()
  },

  checkLoginStatus() {
    const isLogin = app.isLogin()
    const userInfo = app.globalData.userInfo
    
    this.setData({
      isLogin: isLogin,
      userInfo: userInfo
    })
    
    if (isLogin) {
      this.loadUserInfo()
    }
  },

  async loadUserInfo() {
    try {
      const res = await app.request('/user/info')
      if (res.data) {
        this.setData({
          statistics: {
            recipe_count: res.data.recipe_count || 0,
            favorite_count: res.data.favorite_count || 0,
            like_count: res.data.like_count || 0
          }
        })
      }
    } catch (e) {
      console.error('加载用户信息失败', e)
    }
  },

  async handleLogin() {
    wx.showLoading({
      title: '登录中...',
      mask: true
    })
    
    try {
      const res = await app.login()
      wx.hideLoading()
      
      this.setData({
        isLogin: true,
        userInfo: res.user
      })
      
      this.loadUserInfo()
      
      wx.showToast({
        title: '登录成功',
        icon: 'success'
      })
    } catch (e) {
      wx.hideLoading()
      console.error('登录失败', e)
    }
  },

  handleLogout() {
    wx.showModal({
      title: '提示',
      content: '确定要退出登录吗？',
      success: (res) => {
        if (res.confirm) {
          app.globalData.token = ''
          app.globalData.userInfo = null
          wx.removeStorageSync('token')
          wx.removeStorageSync('userInfo')
          
          this.setData({
            isLogin: false,
            userInfo: null,
            statistics: {
              recipe_count: 0,
              favorite_count: 0,
              like_count: 0
            }
          })
          
          wx.showToast({
            title: '已退出登录',
            icon: 'success'
          })
        }
      }
    })
  },

  goToPage(e) {
    const page = e.currentTarget.dataset.page
    
    if (page === 'publish') {
      if (!app.checkLogin()) return
      wx.navigateTo({
        url: '/pages/recipe/publish/publish'
      })
    } else if (page === 'favorites') {
      if (!app.checkLogin()) return
      wx.navigateTo({
        url: '/pages/mine/favorites/favorites'
      })
    } else if (page === 'recipes') {
      if (!app.checkLogin()) return
      wx.navigateTo({
        url: '/pages/mine/recipes/recipes'
      })
    } else if (page === 'about') {
      wx.navigateTo({
        url: '/pages/mine/about/about'
      })
    }
  },

  goToProfile() {
    if (!app.checkLogin()) return
  }
})
