const app = getApp()

Page({
  data: {
    recipeId: 0,
    recipe: null,
    loading: true,
    isLiked: false,
    isFavorited: false
  },

  onLoad(options) {
    const id = options.id ? parseInt(options.id) : 0
    if (id) {
      this.setData({ recipeId: id })
      this.loadRecipeDetail()
    } else {
      wx.showToast({
        title: '参数错误',
        icon: 'none'
      })
      setTimeout(() => {
        wx.navigateBack()
      }, 1500)
    }
  },

  onShow() {
    if (this.data.recipeId) {
      this.loadRecipeDetail()
    }
  },

  async loadRecipeDetail() {
    this.setData({ loading: true })
    
    try {
      const res = await app.request('/recipe/detail', { id: this.data.recipeId })
      
      if (res.data) {
        this.setData({
          recipe: res.data,
          isLiked: res.data.is_liked || false,
          isFavorited: res.data.is_favorited || false,
          loading: false
        })
        
        wx.setNavigationBarTitle({
          title: res.data.title
        })
      }
    } catch (e) {
      this.setData({ loading: false })
      console.error('加载菜谱详情失败', e)
    }
  },

  async toggleLike() {
    if (!app.checkLogin()) return
    
    try {
      const res = await app.request('/recipe/toggleLike', {
        recipe_id: this.data.recipeId
      }, 'POST')
      
      this.setData({
        isLiked: res.data.is_liked,
        'recipe.like_count': res.data.like_count
      })
      
      wx.showToast({
        title: res.data.is_liked ? '点赞成功' : '已取消点赞',
        icon: 'success'
      })
    } catch (e) {
      console.error('点赞失败', e)
    }
  },

  async toggleFavorite() {
    if (!app.checkLogin()) return
    
    try {
      const res = await app.request('/recipe/toggleFavorite', {
        recipe_id: this.data.recipeId
      }, 'POST')
      
      this.setData({
        isFavorited: res.data.is_favorited,
        'recipe.favorite_count': res.data.favorite_count
      })
      
      wx.showToast({
        title: res.data.is_favorited ? '收藏成功' : '已取消收藏',
        icon: 'success'
      })
    } catch (e) {
      console.error('收藏失败', e)
    }
  },

  shareRecipe() {
    wx.showShareMenu({
      withShareTicket: true,
      menus: ['shareAppMessage', 'shareTimeline']
    })
  },

  onShareAppMessage() {
    const recipe = this.data.recipe
    return {
      title: recipe ? `【${recipe.title}】美味菜谱分享` : '美食菜谱小程序',
      path: `/pages/recipe/detail/detail?id=${this.data.recipeId}`,
      imageUrl: recipe ? recipe.image : ''
    }
  },

  goToAuthor() {
    wx.showToast({
      title: '功能开发中',
      icon: 'none'
    })
  },

  goToEdit() {
    const recipe = this.data.recipe
    if (!recipe) return
    
    if (recipe.user_id !== app.globalData.userInfo?.id) {
      wx.showToast({
        title: '只能编辑自己发布的菜谱',
        icon: 'none'
      })
      return
    }
    
    wx.navigateTo({
      url: `/pages/recipe/edit/edit?id=${this.data.recipeId}`
    })
  },

  previewImage(e) {
    const current = e.currentTarget.dataset.url
    const images = this.data.recipe.images || []
    
    if (images.length === 0 && this.data.recipe.image) {
      images.push(this.data.recipe.image)
    }
    
    wx.previewImage({
      current: current,
      urls: images
    })
  }
})
