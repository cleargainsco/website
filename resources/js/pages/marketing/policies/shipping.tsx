import { PolicyArticle } from '@/components/marketing/policy-article';

export default function ShippingPolicy() {
    return (
        <PolicyArticle
            title="Shipping policy"
            summary="How and when orders are packed, dispatched and tracked."
        >
            <h2>Processing</h2>
            <p>
                Orders are packed on business days once payment has been
                confirmed. Orders placed on weekends or holidays are packed on
                the next business day.
            </p>

            <h2>Packaging</h2>
            <p>
                Every vial is sealed, labelled with its batch number and packed
                to protect it from breakage, heat and light in transit.
            </p>

            <h2>Tracking</h2>
            <p>
                When your order ships you'll receive an email with a tracking
                number. Shipping costs are calculated and shown before you pay.
            </p>

            <h2>Delivery issues</h2>
            <p>
                If your tracking stalls, your package arrives damaged, or it
                doesn't arrive at all, contact us with your order number and
                we'll work with the carrier to resolve it.
            </p>
        </PolicyArticle>
    );
}
