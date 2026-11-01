export enum StockStatus {
    InStock = 'in_stock',
    LowStock = 'low_stock',
    OutOfStock = 'out_of_stock',
}

export enum Permission {
    AdminAccess = 'admin.access',
    ProductsManage = 'products.manage',
    OrdersManage = 'orders.manage',
    SeoManage = 'seo.manage',
    MarketingManage = 'marketing.manage',
}

export enum FieldKind {
    Text = 'text',
    Textarea = 'textarea',
}

export enum OrderStatus {
    AwaitingPayment = 'awaiting_payment',
    Paid = 'paid',
    Shipped = 'shipped',
    Cancelled = 'cancelled',
}

export enum SubscriberSource {
    WelcomeOffer = 'welcome_offer',
    Registration = 'registration',
}
