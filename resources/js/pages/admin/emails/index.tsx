import { ExternalLink } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import { cn } from '@/lib/utils';
import { show } from '@/routes/admin/emails';

type EmailPreview = {
    key: string;
    label: string;
    trigger: string;
    subject: string | null;
    from: string;
    to: string[];
    cc: string[];
    bcc: string[];
    reply_to: string[];
};

export default function EmailsIndex({ emails }: { emails: EmailPreview[] }) {
    const [selectedKey, setSelectedKey] = useState(emails[0]?.key);
    const selected = emails.find((email) => email.key === selectedKey);

    return (
        <>
            <div className="mx-auto w-full max-w-6xl space-y-6 p-4 md:p-6">
                <Heading
                    title="Emails"
                    description="What customers and the shop receive, shown with a sample order. Customer emails use your name and address."
                />

                <div className="grid gap-6 lg:grid-cols-[17rem_1fr]">
                    <nav className="space-y-1">
                        {emails.map((email) => (
                            <button
                                key={email.key}
                                type="button"
                                onClick={() => setSelectedKey(email.key)}
                                className={cn(
                                    'w-full rounded-lg px-3 py-2.5 text-left transition-colors',
                                    email.key === selectedKey
                                        ? 'bg-accent text-accent-foreground'
                                        : 'hover:bg-accent/50',
                                )}
                            >
                                <span className="block text-sm font-medium">
                                    {email.label}
                                </span>
                                <span className="block text-xs text-muted-foreground">
                                    {email.trigger}
                                </span>
                            </button>
                        ))}
                    </nav>

                    {selected && (
                        <section className="overflow-hidden rounded-xl border bg-card shadow-xs">
                            <header className="flex items-start justify-between gap-4 border-b px-5 py-4">
                                <dl className="grid min-w-0 grid-cols-[5rem_1fr] gap-x-3 gap-y-1.5 text-sm">
                                    <EnvelopeRow label="From">
                                        {selected.from}
                                    </EnvelopeRow>
                                    <EnvelopeRow label="To">
                                        {selected.to.join(', ')}
                                    </EnvelopeRow>
                                    <EnvelopeRow label="Reply-To">
                                        {selected.reply_to.join(', ') || (
                                            <span className="text-muted-foreground">
                                                Same as From
                                            </span>
                                        )}
                                    </EnvelopeRow>
                                    {selected.cc.length > 0 && (
                                        <EnvelopeRow label="Cc">
                                            {selected.cc.join(', ')}
                                        </EnvelopeRow>
                                    )}
                                    {selected.bcc.length > 0 && (
                                        <EnvelopeRow label="Bcc">
                                            {selected.bcc.join(', ')}
                                        </EnvelopeRow>
                                    )}
                                    <EnvelopeRow label="Subject">
                                        <span className="font-medium">
                                            {selected.subject ?? selected.label}
                                        </span>
                                    </EnvelopeRow>
                                </dl>
                                <a
                                    href={show.url(selected.key)}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="inline-flex shrink-0 items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
                                >
                                    <ExternalLink className="size-4" />
                                    Open in a new tab
                                </a>
                            </header>
                            <iframe
                                key={selected.key}
                                src={show.url(selected.key)}
                                title={`${selected.label} preview`}
                                className="h-[46rem] w-full bg-white"
                            />
                        </section>
                    )}
                </div>
            </div>
        </>
    );
}

function EnvelopeRow({
    label,
    children,
}: {
    label: string;
    children: React.ReactNode;
}) {
    return (
        <>
            <dt className="text-muted-foreground">{label}</dt>
            <dd className="min-w-0 break-words">{children}</dd>
        </>
    );
}
