const app = getApp()

Page({
  data: {
    categoryId: 0,
    keyword: '',
    categoryName: '',
    recipes: [],
    page: 1,
    pageSize: 10,
    total: 0,
    loading: false,
    hasMore: true,
    orderBy: 'new'
  },

  onLoad(options) {
    const categoryId = options.category_id ? parseInt(options.category_id) : 0
    const keyword = options.keyword || ''
    const categoryName = options.category_name || ''
    
    this.setData({
      categoryId: categoryId,
      keyword: keyword,
      categoryName: categoryName
    })
    
    if (keyword) {
      wx.setNavigationBarTitle({
        title: `搜索: ${keyword}`
      })
    } else if (categoryName) {
      wx.setNavigationBarTitle({
        title: categoryName
      })
    }
    
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

  async loadRecipes() {
    if (this.data.loading) return
    
    this.setData({ loading: true })
    
    try {
      const params = {
        page: this.data.page,
        page_size: this.data.pageSize,
        order_by: this.data.orderBy
      }
      
      if (this.data.categoryId > 0) {
        params.category_id = this.data.categoryId
      }
      
      if (this.data.keyword) {
        params.keyword = this.data.keyword
      }
      
      const res = await app.request('/recipe/list', params)
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
      console.error('加载菜谱列表失败', e)
    }
  },

  onOrderChange(e) {
    const orderBy = e.currentTarget.dataset.order
    this.setData({
      orderBy: orderBy,
      page: 1,
      recipes: [],
      hasMore: true
    })
    this.loadRecipes()
  },

  goToRecipeDetail(e) {
    const id = e.currentTarget.dataset.id
    wx.navigateTo({
      url: `/pages/recipe/detail/detail?id=${id}`
    })
  }
})
