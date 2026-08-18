export default function InputError({ messages, className = '' }) {
    if (!messages) return null;

    const msgs = Array.isArray(messages) ? messages : [messages];

    return (
        <ul className={'text-sm text-red-600 space-y-1 ' + className}>
            {msgs.map((message, i) => (
                <li key={i}>{message}</li>
            ))}
        </ul>
    );
}
