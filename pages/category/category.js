const app = getApp()

Page({
  data: {
    categories: [],
    currentCategory: null,
    recipes: [],
    page: 1,
    pageSize: 10,
    total: 0,
    loading: false,
    hasMore: true,
    orderBy: 'new'
  },

  onLoad(options) {
    this.loadCategories()
  },

  onShow() {
    if (typeof this.getTabBar === 'function' && this.getTabBar()) {
      this.getTabBar().setData({
        selected: 1
      })
    }
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

  async loadCategories() {
    try {
      const res = await app.request('/category/list')
      const categories = res.data || []
      
      let defaultCategory = categories[0]
      
      this.setData({
        categories: categories,
        currentCategory: defaultCategory
      })
      
      this.loadRecipes()
    } catch (e) {
      console.error('加载分类失败', e)
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
      
      if (this.data.currentCategory && this.data.currentCategory.id > 0) {
        params.category_id = this.data.currentCategory.id
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

  onCategoryTap(e) {
    const index = e.currentTarget.dataset.index
    const category = this.data.categories[index]
    
    this.setData({
      currentCategory: category,
      page: 1,
      recipes: [],
      hasMore: true
    })
    
    this.loadRecipes()
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
