import Loading from '@/components/loading';
import StarRating from '@/components/StarRating';
import { Badge } from '@/components/ui/badge';
import { useLang } from '@/hooks/useLang';
import { fetchJson } from '@/lib/fetch-json';
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
            fetchJson<{
                questions?: Question[];
                answers?: Record<string, number>;
                self_votes?: Record<string, number>;
            }>(Answer.get(gameId).url)
                .then((data) => {
                    setQuestions(Array.isArray(data.questions) ? data.questions : []);
                    setAnswers(data.answers ?? {});
                    setSelfAnswers(data.self_votes ?? {});
                })
                .catch((error) => {
                    console.error('Failed to load answers', error);
                    setQuestions([]);
                    setAnswers({});
                    setSelfAnswers({});
                })
                .finally(() => {
                    if (withLoading) setIsLoading(false);
                });
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
            <div className="flex md:grid-cols-5 rounded border p-2 md:grid justify-around">
                <div className="col-span-3 font-semibold">
                    {t('game.question')}
                </div>
                <div className="col-span-1 font-semibold">
                    {t('game.public_vote')}
                </div>
                <div className="col-span-1 font-semibold">
                    {t('game.self_vote')}
                </div>
            </div>
            {questions.map((question) => (
                <div
                    key={question.id}
                    className="flex md:grid-cols-5 rounded border p-2 md:grid"
                >
                    <div className="grow md:col-span-3">
                        {question.translations[locale]}
                    </div>
                    <div className="md:col-span-1">
                        <StarRating rank={answers['Q' + question.id] ?? 0} />
                    </div>
                    <div className="md:col-span-1">
                        <div className="flex w-fit ml-5">
                            <Frown className="size-7 stroke-red-500 pr-1" />
                            <StarRating
                                rank={selfAnswers['Q' + question.id] ?? 0}
                                vote={castVote(question.id)}
                            />
                            <Smile className="size-7 stroke-green-500 pl-1" />
                        </div>
                        <Badge
                            variant={
                                status.has(`S${question.id}`)
                                    ? 'constructive'
                                    : status.has(`E${question.id}`)
                                      ? 'destructive'
                                      : 'default'
                            }
                            className={`${!status.has(`S${question.id}`) && !status.has(`E${question.id}`) && 'invisible'} grid mx-auto text-center whitespace-nowrap`}
                        >
                            <div
                                className={`${
                                    status.has(`S${question.id}`)
                                        ? ''
                                        : 'invisible'
                                } col-start-1 row-start-1 w-full justify-center`}
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
                                } col-start-1 row-start-1 flex w-full items-center justify-center`}
                            >
                                {t('game.vote_failure')}
                            </div>
                        </Badge>
                    </div></div>
            ))}
        </div>
    );
}
