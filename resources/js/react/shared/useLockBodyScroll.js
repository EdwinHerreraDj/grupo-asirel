import { useEffect } from "react";

let openCount = 0;
let originalOverflow = null;
let originalPaddingRight = null;

export default function useLockBodyScroll(active = true) {
    useEffect(() => {
        if (!active) return;

        const body = document.body;

        if (openCount === 0) {
            originalOverflow = body.style.overflow;
            originalPaddingRight = body.style.paddingRight;

            const scrollBarWidth =
                window.innerWidth - document.documentElement.clientWidth;
            if (scrollBarWidth > 0) {
                body.style.paddingRight = `${scrollBarWidth}px`;
            }
            body.style.overflow = "hidden";
        }
        openCount += 1;

        return () => {
            openCount -= 1;
            if (openCount <= 0) {
                openCount = 0;
                body.style.overflow = originalOverflow ?? "";
                body.style.paddingRight = originalPaddingRight ?? "";
                originalOverflow = null;
                originalPaddingRight = null;
            }
        };
    }, [active]);
}
