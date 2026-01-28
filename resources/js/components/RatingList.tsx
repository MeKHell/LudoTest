import Loading from '@/components/loading';
import StarRating from '@/components/StarRating';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { useLang } from '@/hooks/useLang';
import Answer from '@/routes/api/answer';
import { Question } from '@/types';
import { useForm } from '@inertiajs/react';
import { Frown, Smile } from 'lucide-react';
import { ReactNode, useCallback, useEffect, useState } from 'react';

export function RatingList({ gameId }: { gameId: string }): ReactNode {
    const { t, locale } = useLang();
    const [isLoading, setIsLoading] = useState<boolean>(false);
    const [questions, setQuestions] = useState<Question[]>([]);
    const [answers, setAnswers] = useState<Record<string, number>>({});
    const [selfAnswers, setSelfAnswers] = useState<Record<string, number>>({});
    const [status, setStatus] = useState<Set<string>>(new Set());
    const [timeouts, setTimeouts] = useState<Record<string, NodeJS.Timeout>>({});
    const { transform, post, processing } = useForm({
        value: -1,
        question_id: -1,
    });
    const loadAnswers = useCallback(
        (withLoading: boolean) => {
            if (withLoading) setIsLoading(true);
            fetch(Answer.get(gameId).url)
                .then((res) => res.json())
                .then((data) => {
                    setQuestions(() => data.questions);
                    setAnswers(() => data.answers);
                    setSelfAnswers(() => data.self_votes);
                })
                .then(() => setIsLoading(false));
        },
        [gameId],
    );

    const updateStatus = useCallback((tik: string) => {
        loadAnswers(false);
        setStatus((old) => new Set(old).add(tik));
        const timeout = setTimeout(
            () => setStatus((old) => new Set(old).difference(new Set([tik]))),
            4000,
        );
        if (timeouts[tik])
            clearTimeout(timeouts[tik]);
        setTimeouts(old => ({...old, [tik]: timeout}));

    }, [loadAnswers, timeouts]);


    const castVote = useCallback(
        (question_id: number) => ( value: number) => {
            if (processing) return; // guard against double‑clicks

            // Optimistically update the UI
            setSelfAnswers(prev => ({
                ...prev,
                [`Q${question_id}`]: value,
            }));

            transform(() => ({
                value, question_id
            }));

            // Fire the request immediately, passing our own callbacks
            post(Answer.post(gameId).url, {
                preserveScroll: true,
                viewTransition: false,
                onSuccess: () => updateStatus(`S${question_id}`),
                onError: () => updateStatus(`E${question_id}`),
            });
        },
        [processing, transform, post, gameId, updateStatus],
    );
    // eslint-disable-next-line react-hooks/set-state-in-effect
    useEffect(() => loadAnswers(true), [loadAnswers]);

    return isLoading ? (
        <Loading />
    ) : (
        <div>
            <div className="grid grid-cols-4 rounded border p-2">
                <div className="col-span-2 font-semibold">
                    {t('game.question')}
                </div>
                <div className="col-span-1 font-semibold">
                    {t('game.public_vote')}
                </div>
                <div className="col-span-1 font-semibold">
                    {t('game.self_vote')}
                </div>
            </div>
            {questions.map((question, i) => (
                <div
                    key={question.id}
                    className="grid grid-cols-4 rounded border p-2"
                >
                    <div className="col-span-2">
                        {question.translations[locale]}
                    </div>
                    <div className="col-span-1">
                        <StarRating rank={answers['Q' + question.id] ?? 0} />
                    </div>
                    <div className="col-span-1 flex">
                        <Frown className="size-7 stroke-red-500 pr-1" />
                        <StarRating
                            rank={selfAnswers['Q' + question.id] ?? 0}
                            vote={castVote(question.id)}
                        />
                        <Smile className="size-7 stroke-green-500 pl-1" />

                        <Badge
                            variant={
                                status.has(`S${question.id}`)
                                    ? 'constructive'
                                    : status.has(`E${question.id}`)
                                      ? 'destructive'
                                      : 'default'
                            }
                            className={`${!status.has(`S${question.id}`) && !status.has(`S${question.id}`) && 'invisible'} grid whitespace-nowrap text-center`}
                        >
                            <div
                                className={`${
                                    status.has(`S${question.id}`)
                                        ? ''
                                        : 'invisible'
                                } row-start-1 col-start-1 justify-center w-full`}
                                aria-live="polite"
                            >
                                {t('game.successful_vote')}
                            </div>

                            {/* ERROR MESSAGE – same spot, hidden when not active */}
                            <div
                                className={`${
                                    status.has(`E${question.id}`) &&
                                    !status.has(`S${question.id}`)
                                        ? ''
                                        : 'invisible'
                                } col-start-1 row-start-1 flex items-center justify-center w-full`}
                                aria-live="polite"
                            >
                                {t('game.vote_failure')}
                            </div>
                        </Badge>
                    </div>
                    {i !== questions.length - 1 && <Separator />}
                </div>
            ))}
        </div>
    );
}
