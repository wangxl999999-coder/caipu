const app = getApp()

Page({
  data: {
    version: '1.0.0',
    copyright: '© 2026 美食菜谱小程序'
  },

  onLoad() {
    
  },

  callPhone() {
    wx.makePhoneCall({
      phoneNumber: '400-123-4567'
    })
  },

  copyWechat() {
    wx.setClipboardData({
      data: 'caipu_app',
      success: () => {
        wx.showToast({
          title: '已复制微信号',
          icon: 'success'
        })
      }
    })
  },

  openMap() {
    wx.openLocation({
      latitude: 39.9042,
      longitude: 116.4074,
      name: '美食菜谱总部',
      address: '北京市朝阳区美食大厦12层'
    })
  },

  sendFeedback() {
    wx.showModal({
      title: '意见反馈',
      content: '如有问题或建议，请联系我们',
      confirmText: '联系客服',
      success: (res) => {
        if (res.confirm) {
          wx.makePhoneCall({
            phoneNumber: '400-123-4567'
          })
        }
      }
    })
  },

  showAgreement() {
    wx.showModal({
      title: '用户协议',
      content: '欢迎使用美食菜谱小程序！\n\n使用本应用即表示您同意我们的用户协议。我们承诺保护您的隐私，不会泄露您的个人信息。\n\n本应用中的菜谱内容仅供参考，实际操作请根据个人情况调整。',
      showCancel: false
    })
  },

  showPrivacy() {
    wx.showModal({
      title: '隐私政策',
      content: '我们非常重视您的隐私保护。\n\n本应用仅收集必要的用户信息，包括：\n1. 微信授权的昵称、头像\n2. 您发布的菜谱内容\n3. 您的收藏、点赞记录\n\n所有信息仅用于提供更好的服务体验，不会向第三方泄露。',
      showCancel: false
    })
  },

  checkUpdate() {
    const updateManager = wx.getUpdateManager()
    
    updateManager.onCheckForUpdate(function (res) {
      if (!res.hasUpdate) {
        wx.showToast({
          title: '已是最新版本',
          icon: 'none'
        })
      }
    })
    
    updateManager.onUpdateReady(function () {
      wx.showModal({
        title: '更新提示',
        content: '新版本已经准备好，是否重启应用？',
        success: function (res) {
          if (res.confirm) {
            updateManager.applyUpdate()
          }
        }
      })
    })
    
    updateManager.onUpdateFailed(function () {
      wx.showToast({
        title: '更新失败',
        icon: 'none'
      })
    })
  },

  goToHome() {
    wx.switchTab({
      url: '/pages/index/index'
    })
  }
})
