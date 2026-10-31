import { PolicyArticle } from '@/components/marketing/policy-article';

export default function TermsOfService() {
    return (
        <PolicyArticle
            title="Terms of service"
            summary="The terms that apply when you use this site or buy from Clear Gains."
        >
            <h2>Research use only</h2>
            <p>
                All products are sold for laboratory research use only. They are
                not drugs, foods, cosmetics or dietary supplements, and are not
                for human or veterinary consumption. By ordering, you confirm
                you will use them only for lawful research purposes and handle
                them according to appropriate laboratory practice.
            </p>

            <h2>Eligibility</h2>
            <p>
                You must be of legal age in your jurisdiction to buy from us. We
                may refuse or cancel any order, including one that indicates use
                outside of research.
            </p>

            <h2>Pricing and orders</h2>
            <p>
                Prices are listed per vial in US dollars. Bulk pricing applies
                to vials of the same product. Tax and shipping are additional.
                We may correct pricing errors, and an order is accepted only
                once we confirm it.
            </p>

            <h2>Product information</h2>
            <p>
                We work to keep product descriptions, storage guidance and
                documentation accurate. Certificates of analysis describe the
                specific batch tested.
            </p>

            <h2>Limitation of liability</h2>
            <p>
                To the extent permitted by law, Clear Gains is not liable for
                any misuse of its products, or for indirect or consequential
                losses arising from their use.
            </p>

            <h2>Changes</h2>
            <p>
                We may update these terms from time to time. The version posted
                on this page applies to orders placed while it is posted.
            </p>
        </PolicyArticle>
    );
}
