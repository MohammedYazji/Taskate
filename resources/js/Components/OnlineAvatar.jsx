export default function OnlineAvatar({ member, online = false, size = "w-7 h-7" }) {
    const dotSize = size === "w-7 h-7" ? "w-2.5 h-2.5" : "w-2 h-2";
    return (
        <div className={`relative inline-flex flex-shrink-0 ${size}`}>
            {member.avatar ? (
                <img src={member.avatar} alt={member.name} className={`${size} rounded-full object-cover`} />
            ) : (
                <div className={`${size} rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-xs font-semibold`}>
                    {member.name?.charAt(0).toUpperCase()}
                </div>
            )}
            {online && (
                <span className={`absolute -bottom-0.5 -right-0.5 ${dotSize} rounded-full bg-green-500 ring-2 ring-white`} />
            )}
        </div>
    );
}
