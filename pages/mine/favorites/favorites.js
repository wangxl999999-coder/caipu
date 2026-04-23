const app = getApp()

Page({
  data: {
    recipes: [],
    page: 1,
    pageSize: 10,
    total: 0,
    loading: true,
    hasMore: true
  },

  onLoad() {
    this.loadFavorites()
  },

  onShow() {
    this.setData({
      page: 1,
      recipes: [],
      hasMore: true
    })
    this.loadFavorites()
  },

  onPullDownRefresh() {
    this.setData({
      page: 1,
      recipes: [],
      hasMore: true
    })
    this.loadFavorites().then(() => {
      wx.stopPullDownRefresh()
    })
  },

  onReachBottom() {
    if (this.data.hasMore && !this.data.loading) {
      this.loadFavorites()
    }
  },

  async loadFavorites() {
    if (this.data.loading && this.data.recipes.length > 0) return
    
    this.setData({ loading: true })
    
    try {
      const res = await app.request('/user/myFavorites', {
        page: this.data.page,
        page_size: this.data.pageSize
      })
      
      const newRecipes = res.data.list || []
      
      this.setData({
        recipes: this.data.page === 1 ? newRecipes : [...this.data.recipes, ...newRecipes],
        total: res.data.total,
        page: this.data.page + 1,
        hasMore: newRecipes.length >= this.data.pageSize,
        loading: false
      })
    } catch (e) {
      this.setData({ loading: false })
      console.error('加载收藏列表失败', e)
    }
  },

  goToRecipeDetail(e) {
    const id = e.currentTarget.dataset.id
    wx.navigateTo({
      url: `/pages/recipe/detail/detail?id=${id}`
    })
  },

  async toggleFavorite(e) {
    const id = e.currentTarget.dataset.id
    const index = e.currentTarget.dataset.index
    
    try {
      const res = await app.request('/recipe/toggleFavorite', {
        recipe_id: id
      }, 'POST')
      
      if (!res.data.is_favorited) {
        const recipes = [...this.data.recipes]
        recipes.splice(index, 1)
        this.setData({ recipes })
        
        wx.showToast({
          title: '已取消收藏',
          icon: 'success'
        })
      }
    } catch (e) {
      console.error('取消收藏失败', e)
    }
  }
})
