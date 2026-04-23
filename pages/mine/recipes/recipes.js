const app = getApp()

Page({
  data: {
    recipes: [],
    page: 1,
    pageSize: 10,
    total: 0,
    loading: true,
    hasMore: true,
    currentStatus: -1
  },

  onLoad() {
    this.loadRecipes()
  },

  onShow() {
    this.setData({
      page: 1,
      recipes: [],
      hasMore: true
    })
    this.loadRecipes()
  },

  onPullDownRefresh() {
    this.setData({
      page: 1,
      recipes: [],
      hasMore: true
    })
    this.loadRecipes().then(() => {
      wx.stopPullDownRefresh()
    })
  },

  onReachBottom() {
    if (this.data.hasMore && !this.data.loading) {
      this.loadRecipes()
    }
  },

  onStatusChange(e) {
    const status = parseInt(e.currentTarget.dataset.status)
    this.setData({
      currentStatus: status,
      page: 1,
      recipes: [],
      hasMore: true
    })
    this.loadRecipes()
  },

  async loadRecipes() {
    if (this.data.loading && this.data.recipes.length > 0) return
    
    this.setData({ loading: true })
    
    try {
      const params = {
        page: this.data.page,
        page_size: this.data.pageSize
      }
      
      if (this.data.currentStatus >= 0) {
        params.status = this.data.currentStatus
      }
      
      const res = await app.request('/user/myRecipes', params)
      
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
      console.error('加载我的菜谱失败', e)
    }
  },

  goToRecipeDetail(e) {
    const id = e.currentTarget.dataset.id
    wx.navigateTo({
      url: `/pages/recipe/detail/detail?id=${id}`
    })
  },

  goToEdit(e) {
    const id = e.currentTarget.dataset.id
    wx.navigateTo({
      url: `/pages/recipe/edit/edit?id=${id}`
    })
  },

  getStatusText(status) {
    const texts = {
      0: '待审核',
      1: '已发布',
      2: '已下架'
    }
    return texts[status] || '未知'
  },

  getStatusClass(status) {
    const classes = {
      0: 'status-pending',
      1: 'status-published',
      2: 'status-offline'
    }
    return classes[status] || ''
  }
})
