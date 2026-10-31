import { PolicyArticle } from '@/components/marketing/policy-article';

export default function ReturnsPolicy() {
    return (
        <PolicyArticle
            title="Returns policy"
            summary="What we can accept back, and how we handle damaged or incorrect orders."
        >
            <h2>Why returns are limited</h2>
            <p>
                Research materials can't be verified once they've left our
                control, so we're unable to accept returns of vials that have
                been opened, reconstituted or stored outside our guidance.
            </p>

            <h2>Damaged or incorrect items</h2>
            <p>
                If an item arrives damaged, or you received the wrong product or
                quantity, contact us as soon as possible. Please include:
            </p>
            <ul>
                <li>Your order number</li>
                <li>Photos of the item, label and packaging</li>
                <li>The batch number shown on the vial</li>
            </ul>
            <p>
                Once we've reviewed it, we'll arrange a replacement or a refund
                to your original payment method.
            </p>

            <h2>Cancellations</h2>
            <p>
                Orders can be cancelled free of charge until they have been
                packed. Contact us as soon as possible if you need to make a
                change.
            </p>
        </PolicyArticle>
    );
}
