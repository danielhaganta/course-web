export default function AppLogo() {
    return (
        <>
            {/* Saat sidebar diciutkan, cukup huruf P */}
            <span className="hidden w-8 text-center font-display text-2xl font-black group-data-[collapsible=icon]:block">
                P
            </span>
            <span className="flex items-baseline gap-2 group-data-[collapsible=icon]:hidden">
                <span className="font-display text-2xl font-black tracking-[0.08em]">
                    POIEMA
                </span>
                <span className="text-[11px] tracking-[0.14em] text-muted-foreground uppercase">
                    Academy
                </span>
            </span>
        </>
    );
}
