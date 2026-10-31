import { PolicyArticle } from '@/components/marketing/policy-article';

export default function PrivacyPolicy() {
    return (
        <PolicyArticle
            title="Privacy policy"
            summary="What information we collect, how we use it and the choices you have."
        >
            <h2>Information we collect</h2>
            <ul>
                <li>
                    Contact details you give us, such as your name, email
                    address and shipping address.
                </li>
                <li>
                    Order details, including the products you buy and your order
                    history.
                </li>
                <li>
                    Basic technical information, such as your browser type and
                    the pages you visit, collected through cookies needed to run
                    the site.
                </li>
            </ul>

            <h2>How we use it</h2>
            <p>
                We use your information to process and ship orders, respond to
                your messages, send documentation you request and keep our site
                secure. We don't sell your personal information.
            </p>

            <h2>Payments</h2>
            <p>
                Payments are handled by our payment processor. We don't store
                full card details on our servers.
            </p>

            <h2>Sharing</h2>
            <p>
                We share information only with the service providers that help
                us operate, such as shipping carriers and payment processors,
                and only as needed for them to do so, or where the law requires
                it.
            </p>

            <h2>Retention and your choices</h2>
            <p>
                We keep order records for as long as needed for accounting and
                legal purposes. You can ask us to access, correct or delete the
                personal information we hold about you.
            </p>
        </PolicyArticle>
    );
}
