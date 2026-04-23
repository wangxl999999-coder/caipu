const app = getApp()

Page({
  data: {
    dailyRecipes: [],
    recipes: [],
    categories: [],
    keyword: '',
    page: 1,
    pageSize: 10,
    total: 0,
    loading: false,
    hasMore: true
  },

  onLoad() {
    this.initData()
  },

  onShow() {
    if (typeof this.getTabBar === 'function' && this.getTabBar()) {
      this.getTabBar().setData({
        selected: 0
      })
    }
  },

  onPullDownRefresh() {
    this.setData({
      page: 1,
      recipes: [],
      hasMore: true
    })
    this.initData().then(() => {
      wx.stopPullDownRefresh()
    })
  },

  onReachBottom() {
    if (this.data.hasMore && !this.data.loading) {
      this.loadRecipes()
    }
  },

  async initData() {
    await Promise.all([
      this.loadDailyRecipes(),
      this.loadCategories(),
      this.loadRecipes()
    ])
  },

  async loadDailyRecipes() {
    try {
      const res = await app.request('/recipe/daily')
      this.setData({
        dailyRecipes: res.data || []
      })
    } catch (e) {
      console.error('加载每日推荐失败', e)
    }
  },

  async loadCategories() {
    try {
      const res = await app.request('/category/list')
      this.setData({
        categories: res.data || []
      })
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
        order_by: 'new'
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

  onSearchInput(e) {
    this.setData({
      keyword: e.detail.value
    })
  },

  onSearch() {
    this.setData({
      page: 1,
      recipes: [],
      hasMore: true
    })
    this.loadRecipes()
  },

  onClearSearch() {
    this.setData({
      keyword: '',
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
  },

  goToCategory(e) {
    const id = e.currentTarget.dataset.id
    if (id === 0) {
      wx.navigateTo({
        url: '/pages/recipe/list/list'
      })
    } else {
      wx.navigateTo({
        url: `/pages/recipe/list/list?category_id=${id}`
      })
    }
  },

  goToSearch() {
    wx.navigateTo({
      url: '/pages/recipe/list/list?keyword=' + encodeURIComponent(this.data.keyword)
    })
  }
})
